<?php

namespace App\Tests;

use App\Entity\Generation;
use App\Entity\Plan;
use App\Entity\Tool;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Vérifie le contrôle d'accès basé sur les plans :
 * - Un outil non inclus dans le plan retourne 403
 * - Un outil inclus dans le plan retourne 200
 * - Le quota journalier bloque la génération quand il est atteint
 */
class AccessControlTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em     = static::getContainer()->get('doctrine')->getManager();

        // Nettoyage dans l'ordre des dépendances (FK : pdf_queue → user → plan)
        foreach ($this->em->getRepository(\App\Entity\PdfQueue::class)->findAll() as $q) {
            $this->em->remove($q);
        }
        foreach ($this->em->getRepository(Generation::class)->findAll() as $g) {
            $this->em->remove($g);
        }
        foreach ($this->em->getRepository(User::class)->findAll() as $u) {
            $this->em->remove($u);
        }
        $this->em->flush();

        foreach ($this->em->getRepository(Plan::class)->findAll() as $p) {
            $this->em->remove($p);
        }
        foreach ($this->em->getRepository(Tool::class)->findAll() as $t) {
            $this->em->remove($t);
        }
        $this->em->flush();
    }

    private function createTool(string $name): Tool
    {
        $tool = new Tool();
        $tool->setName($name);
        $tool->setIcon('fa-solid fa-file');
        $tool->setDescription($name . ' tool');
        $tool->setIsActive(true);
        $this->em->persist($tool);

        return $tool;
    }

    private function createPlan(string $name, int $limit, array $tools = []): Plan
    {
        $plan = new Plan();
        $plan->setName($name);
        $plan->setDescription($name . ' plan');
        $plan->setLimitGeneration($limit);
        $plan->setRole('ROLE_' . strtoupper($name));
        $plan->setPrice(0.0);
        $plan->setActive(true);
        $plan->setCreatedAt(new \DateTimeImmutable());

        foreach ($tools as $tool) {
            $plan->addTool($tool);
        }

        $this->em->persist($plan);

        return $plan;
    }

    private function createUser(string $email, Plan $plan): User
    {
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setPlan($plan);
        $user->setPassword($hasher->hashPassword($user, 'password123'));
        $this->em->persist($user);

        return $user;
    }

    // -------------------------------------------------------------------------
    // Tests de contrôle d'accès par plan
    // -------------------------------------------------------------------------

    public function testFreeUserCanAccessToolIncludedInPlan(): void
    {
        $urlTool = $this->createTool('URL to PDF');
        $plan    = $this->createPlan('FREE', 10, [$urlTool]);
        $user    = $this->createUser('free@test.com', $plan);
        $this->em->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/convert/url');

        self::assertResponseIsSuccessful('Un utilisateur FREE doit pouvoir accéder à un outil inclus dans son plan');
    }

    public function testFreeUserCannotAccessToolNotInPlan(): void
    {
        $urlTool   = $this->createTool('URL to PDF');
        $splitTool = $this->createTool('Split PDF');
        $plan      = $this->createPlan('FREE', 10, [$urlTool]); // Split PDF non inclus
        $user      = $this->createUser('free2@test.com', $plan);
        $this->em->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/convert/split');

        self::assertResponseStatusCodeSame(403, 'Un utilisateur FREE doit recevoir 403 pour un outil hors de son plan');
    }

    public function testPremiumUserCanAccessAllTools(): void
    {
        $urlTool   = $this->createTool('URL to PDF');
        $splitTool = $this->createTool('Split PDF');
        $mergeTool = $this->createTool('Merge PDF');
        $plan      = $this->createPlan('PREMIUM', -1, [$urlTool, $splitTool, $mergeTool]);
        $user      = $this->createUser('premium@test.com', $plan);
        $this->em->flush();

        $this->client->loginUser($user);

        $this->client->request('GET', '/convert/url');
        self::assertResponseIsSuccessful('PREMIUM doit accéder à URL to PDF');

        $this->client->request('GET', '/convert/split');
        self::assertResponseIsSuccessful('PREMIUM doit accéder à Split PDF');

        $this->client->request('GET', '/convert/merge');
        self::assertResponseIsSuccessful('PREMIUM doit accéder à Merge PDF');
    }

    public function testAuthenticatedUserCanAccessHistory(): void
    {
        $plan = $this->createPlan('FREE', 10);
        $user = $this->createUser('history@test.com', $plan);
        $this->em->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/account/history');

        self::assertResponseIsSuccessful('Un utilisateur connecté doit pouvoir voir son historique');
    }

    // -------------------------------------------------------------------------
    // Tests de quota journalier
    // -------------------------------------------------------------------------

    public function testGenerationIsBlockedWhenDailyQuotaReached(): void
    {
        $urlTool = $this->createTool('URL to PDF');
        $plan    = $this->createPlan('FREE', 1, [$urlTool]); // limite = 1
        $user    = $this->createUser('quota@test.com', $plan);

        // Ajouter 1 génération aujourd'hui → quota atteint
        $generation = new Generation();
        $generation->setUser($user);
        $generation->setFile('test.pdf');
        $generation->setToolName('URL to PDF');
        $generation->setCreateadAt(new \DateTimeImmutable());
        $this->em->persist($generation);
        $this->em->flush();

        $this->client->loginUser($user);

        // GET pour récupérer le formulaire et le token CSRF
        $crawler = $this->client->request('GET', '/convert/url');
        self::assertResponseIsSuccessful();

        // POST avec un URL valide → doit être bloqué par le quota
        $this->client->submitForm('Générer le PDF', [
            'form[url]' => 'https://example.com',
        ]);

        // Doit rediriger (quota dépassé) et non générer le PDF
        self::assertResponseRedirects('/convert/url', null, 'Le dépassement de quota doit rediriger l\'utilisateur');
    }

    public function testGenerationIsAllowedWhenQuotaNotReached(): void
    {
        $urlTool = $this->createTool('URL to PDF');
        $plan    = $this->createPlan('FREE', 5, [$urlTool]); // limite = 5
        $user    = $this->createUser('quota2@test.com', $plan);
        $this->em->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/convert/url');

        // Le formulaire doit s'afficher (quota non atteint)
        self::assertResponseIsSuccessful('Le formulaire doit s\'afficher quand le quota n\'est pas atteint');
    }
}
