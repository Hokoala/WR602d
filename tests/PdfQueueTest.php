<?php

namespace App\Tests;

use App\Entity\PdfQueue;
use App\Entity\Plan;
use App\Entity\User;
use App\Repository\PdfQueueRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Tests unitaires pour l'entité PdfQueue et fonctionnels pour le repository.
 */
class PdfQueueTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();

        // Nettoyage
        foreach ($this->em->getRepository(PdfQueue::class)->findAll() as $q) {
            $this->em->remove($q);
        }
        foreach ($this->em->getRepository(User::class)->findAll() as $u) {
            $this->em->remove($u);
        }
        foreach ($this->em->getRepository(Plan::class)->findAll() as $p) {
            $this->em->remove($p);
        }
        $this->em->flush();
    }

    private function createUserWithPlan(): User
    {
        $plan = new Plan();
        $plan->setName('TEST');
        $plan->setDescription('Plan test');
        $plan->setLimitGeneration(10);
        $plan->setRole('ROLE_TEST');
        $plan->setPrice(0.0);
        $plan->setActive(true);
        $plan->setCreatedAt(new \DateTimeImmutable());
        $this->em->persist($plan);

        $user = new User();
        $user->setEmail('queue_test@example.com');
        $user->setPassword('hashed');
        $user->setPlan($plan);
        $this->em->persist($user);

        $this->em->flush();

        return $user;
    }

    // -------------------------------------------------------------------------
    // Tests unitaires de l'entité PdfQueue
    // -------------------------------------------------------------------------

    public function testPdfQueueEntityDefaults(): void
    {
        $queue = new PdfQueue();
        $queue->setToken('test_token_123');
        $queue->setInputFiles(['file1.pdf', 'file2.pdf']);
        $queue->setCreatedAt(new \DateTimeImmutable('2026-01-01'));

        self::assertSame('test_token_123', $queue->getToken());
        self::assertSame(['file1.pdf', 'file2.pdf'], $queue->getInputFiles());
        self::assertSame('pending', $queue->getStatus(), 'Le statut par défaut doit être "pending"');
        self::assertNull($queue->getResultFile(), 'Le fichier résultat doit être null par défaut');
        self::assertNull($queue->getProcessedAt(), 'processedAt doit être null par défaut');
        self::assertNull($queue->getId(), 'L\'id doit être null avant la persistance');
    }

    public function testPdfQueueStatusTransitions(): void
    {
        $queue = new PdfQueue();
        $queue->setToken('token');
        $queue->setInputFiles([]);
        $queue->setCreatedAt(new \DateTimeImmutable());

        $queue->setStatus('processing');
        self::assertSame('processing', $queue->getStatus());

        $queue->setStatus('done');
        $queue->setResultFile('merged_result.pdf');
        $queue->setProcessedAt(new \DateTimeImmutable());

        self::assertSame('done', $queue->getStatus());
        self::assertSame('merged_result.pdf', $queue->getResultFile());
        self::assertNotNull($queue->getProcessedAt());
    }

    public function testPdfQueueFailedStatus(): void
    {
        $queue = new PdfQueue();
        $queue->setToken('token');
        $queue->setInputFiles([]);
        $queue->setCreatedAt(new \DateTimeImmutable());
        $queue->setStatus('failed');
        $queue->setProcessedAt(new \DateTimeImmutable());

        self::assertSame('failed', $queue->getStatus());
        self::assertNull($queue->getResultFile(), 'Un item failed ne doit pas avoir de fichier résultat');
    }

    // -------------------------------------------------------------------------
    // Tests fonctionnels du repository
    // -------------------------------------------------------------------------

    public function testCountPendingByUserReturnsZeroForNewUser(): void
    {
        $user = $this->createUserWithPlan();

        /** @var PdfQueueRepository $repo */
        $repo  = $this->em->getRepository(PdfQueue::class);
        $count = $repo->countPendingByUser($user);

        self::assertSame(0, $count, 'Un nouvel utilisateur ne doit avoir aucun item en attente');
    }

    public function testCountPendingByUserCountsOnlyPendingItems(): void
    {
        $user = $this->createUserWithPlan();

        // 2 items pending
        for ($i = 0; $i < 2; $i++) {
            $q = new PdfQueue();
            $q->setUser($user);
            $q->setToken('token_pending_' . $i);
            $q->setInputFiles(['file.pdf']);
            $q->setCreatedAt(new \DateTimeImmutable());
            $this->em->persist($q);
        }

        // 1 item done (ne doit pas être compté)
        $done = new PdfQueue();
        $done->setUser($user);
        $done->setToken('token_done');
        $done->setInputFiles(['file.pdf']);
        $done->setStatus('done');
        $done->setResultFile('result.pdf');
        $done->setCreatedAt(new \DateTimeImmutable());
        $done->setProcessedAt(new \DateTimeImmutable());
        $this->em->persist($done);

        $this->em->flush();

        /** @var PdfQueueRepository $repo */
        $repo  = $this->em->getRepository(PdfQueue::class);
        $count = $repo->countPendingByUser($user);

        self::assertSame(2, $count, 'Seuls les items "pending" doivent être comptés');
    }

    public function testCountPendingByUserDoesNotCountOtherUsersItems(): void
    {
        $user1 = $this->createUserWithPlan();

        // Créer un second utilisateur
        $plan2 = new Plan();
        $plan2->setName('TEST2');
        $plan2->setDescription('Plan test 2');
        $plan2->setLimitGeneration(10);
        $plan2->setRole('ROLE_TEST2');
        $plan2->setPrice(0.0);
        $plan2->setActive(true);
        $plan2->setCreatedAt(new \DateTimeImmutable());
        $this->em->persist($plan2);

        $user2 = new User();
        $user2->setEmail('other_user@example.com');
        $user2->setPassword('hashed');
        $user2->setPlan($plan2);
        $this->em->persist($user2);

        // Item pending pour user2
        $q = new PdfQueue();
        $q->setUser($user2);
        $q->setToken('token_other_user');
        $q->setInputFiles(['file.pdf']);
        $q->setCreatedAt(new \DateTimeImmutable());
        $this->em->persist($q);

        $this->em->flush();

        /** @var PdfQueueRepository $repo */
        $repo  = $this->em->getRepository(PdfQueue::class);
        $count = $repo->countPendingByUser($user1);

        self::assertSame(0, $count, 'Les items des autres utilisateurs ne doivent pas être comptés');
    }

    public function testFindPendingReturnsItemsOrderedByDate(): void
    {
        $user = $this->createUserWithPlan();

        $older = new PdfQueue();
        $older->setUser($user);
        $older->setToken('token_older');
        $older->setInputFiles(['a.pdf']);
        $older->setCreatedAt(new \DateTimeImmutable('2026-01-01 10:00:00'));
        $this->em->persist($older);

        $newer = new PdfQueue();
        $newer->setUser($user);
        $newer->setToken('token_newer');
        $newer->setInputFiles(['b.pdf']);
        $newer->setCreatedAt(new \DateTimeImmutable('2026-01-01 11:00:00'));
        $this->em->persist($newer);

        $this->em->flush();

        /** @var PdfQueueRepository $repo */
        $repo  = $this->em->getRepository(PdfQueue::class);
        $items = $repo->findPending(10);

        self::assertCount(2, $items);
        self::assertSame('token_older', $items[0]->getToken(), 'Le plus ancien doit être traité en premier (FIFO)');
        self::assertSame('token_newer', $items[1]->getToken());
    }
}
