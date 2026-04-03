<?php

namespace App\Tests;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // Ensure we have a clean database
        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();
        $this->em = $em;

        $this->userRepository = $container->get(UserRepository::class);

        foreach ($this->em->getRepository(\App\Entity\PdfQueue::class)->findAll() as $q) {
            $this->em->remove($q);
        }
        foreach ($this->em->getRepository(ResetPasswordRequest::class)->findAll() as $r) {
            $this->em->remove($r);
        }
        foreach ($this->userRepository->findAll() as $user) {
            $this->em->remove($user);
        }

        $this->em->flush();
    }

    public function testResetPasswordController(): void
    {
        // Create a plan (required by User entity)
        $plan = new \App\Entity\Plan();
        $plan->setName('FREE');
        $plan->setDescription('Plan test');
        $plan->setLimitGeneration(4);
        $plan->setRole('ROLE_FREE');
        $plan->setPrice(0.0);
        $plan->setActive(true);
        $plan->setCreatedAt(new \DateTimeImmutable());
        $this->em->persist($plan);

        // Create a test user
        $user = (new User())
            ->setEmail('me@example.com')
            ->setPassword('a-test-password-that-will-be-changed-later')
        ;
        $user->setPlan($plan);
        $this->em->persist($user);
        $this->em->flush();

        // Test Request reset password page
        $this->client->request('GET', '/reset-password');

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains('Mot de passe oublié');

        // Submit the reset password form and test email message is queued / sent
        $this->client->submitForm('ENVOYER LE LIEN', [
            'reset_password_request_form[email]' => 'me@example.com',
        ]);

        // Ensure the reset password email was sent
        // Use either assertQueuedEmailCount() || assertEmailCount() depending on your mailer setup
        // self::assertQueuedEmailCount(1);
        self::assertEmailCount(1);

        $messages = $this->getMailerMessages();
        self::assertNotEmpty($messages);

        self::assertEmailAddressContains($messages[0], 'from', 'jean-michel.le@etudiant.univ-reims.fr');
        self::assertEmailAddressContains($messages[0], 'to', 'me@example.com');
        self::assertEmailTextBodyContains($messages[0], 'This link will expire in 1 hour.');

        self::assertResponseRedirects('/reset-password/check-email');

        // Test check email landing page shows correct "expires at" time
        $crawler = $this->client->followRedirect();

        self::assertPageTitleContains('Email envoyé');
        self::assertStringContainsString('Ce lien expirera dans', $crawler->html());

        // Test the link sent in the email is valid
        $email = $messages[0]->toString();
        preg_match('#(/reset-password/reset/[a-zA-Z0-9]+)#', $email, $resetLink);

        $this->client->request('GET', $resetLink[1]);

        self::assertResponseRedirects('/reset-password/reset');

        $this->client->followRedirect();

        // Test we can set a new password
        $this->client->submitForm('RÉINITIALISER', [
            'change_password_form[plainPassword][first]' => 'newStrongPassword',
            'change_password_form[plainPassword][second]' => 'newStrongPassword',
        ]);

        self::assertResponseRedirects('/home');

        $user = $this->userRepository->findOneBy(['email' => 'me@example.com']);

        self::assertInstanceOf(User::class, $user);

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($passwordHasher->isPasswordValid($user, 'newStrongPassword'));
    }
}
