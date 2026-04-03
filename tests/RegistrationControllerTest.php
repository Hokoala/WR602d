<?php

namespace App\Tests;

use App\Entity\Plan;
use App\Entity\ResetPasswordRequest;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;
    private EntityManagerInterface $em;
    private Plan $plan;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $this->em           = $container->get('doctrine')->getManager();
        $this->userRepository = $container->get(UserRepository::class);

        // Nettoyage (FK : pdf_queue / reset_password_request → user → plan)
        foreach ($this->em->getRepository(\App\Entity\PdfQueue::class)->findAll() as $q) {
            $this->em->remove($q);
        }
        foreach ($this->em->getRepository(ResetPasswordRequest::class)->findAll() as $r) {
            $this->em->remove($r);
        }
        foreach ($this->userRepository->findAll() as $user) {
            $this->em->remove($user);
        }
        foreach ($this->em->getRepository(Plan::class)->findAll() as $p) {
            $this->em->remove($p);
        }
        $this->em->flush();
        $this->em->clear(); // vide l'identity map Doctrine

        // Créer un plan FREE pour le formulaire d'inscription
        $this->plan = new Plan();
        $this->plan->setName('FREE');
        $this->plan->setDescription('Plan gratuit');
        $this->plan->setLimitGeneration(4);
        $this->plan->setRole('ROLE_FREE');
        $this->plan->setPrice(0.0);
        $this->plan->setActive(true);
        $this->plan->setCreatedAt(new \DateTimeImmutable());
        $this->em->persist($this->plan);
        $this->em->flush();
    }

    public function testRegister(): void
    {
        $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Inscription');

        $this->client->submitForm('CRÉER MON COMPTE', [
            'registration_form[email]'         => 'me@example.com',
            'registration_form[plainPassword]' => 'password',
            'registration_form[agreeTerms]'    => true,
        ]);

        $allUsers = $this->userRepository->findAll();
        $emails   = implode(', ', array_map(fn($u) => $u->getEmail(), $allUsers));
        self::assertCount(1, $allUsers, "Expected 1 user, found: [{$emails}]");
        self::assertFalse(($user = $allUsers[0])->isVerified());

        self::assertEmailCount(1);

        $messages = $this->getMailerMessages();
        self::assertNotEmpty($messages);
        self::assertEmailAddressContains($messages[0], 'from', 'jean-michel.le@etudiant.univ-reims.fr');
        self::assertEmailAddressContains($messages[0], 'to', 'me@example.com');

        $this->client->followRedirect();
        $this->client->loginUser($user);

        /** @var TemplatedEmail $templatedEmail */
        $templatedEmail = $messages[0];
        $messageBody    = $templatedEmail->getHtmlBody();
        self::assertIsString($messageBody);

        preg_match('#(http://localhost/verify/email.+)">#', $messageBody, $resetLink);

        $this->client->request('GET', $resetLink[1]);
        $this->client->followRedirect();

        self::assertTrue(static::getContainer()->get(UserRepository::class)->findAll()[0]->isVerified());
    }
}
