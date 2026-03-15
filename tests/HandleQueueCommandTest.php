<?php

namespace App\Tests;

use App\Command\HandleQueueCommand;
use App\Entity\PdfQueue;
use App\Repository\PdfQueueRepository;
use App\Service\YourGotenbergService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Tests unitaires pour la commande app:handle-queue.
 * Utilise des mocks pour isoler la logique sans base de données ni Gotenberg.
 */
class HandleQueueCommandTest extends TestCase
{
    private function makeCommand(
        array $pendingItems,
        YourGotenbergService $pdfService = null,
        EntityManagerInterface $em = null,
        string $projectDir = '/tmp',
    ): HandleQueueCommand {
        $repo = $this->createMock(PdfQueueRepository::class);
        $repo->method('findPending')->willReturn($pendingItems);

        return new HandleQueueCommand(
            $repo,
            $pdfService ?? $this->createMock(YourGotenbergService::class),
            $em         ?? $this->createMock(EntityManagerInterface::class),
            $projectDir,
        );
    }

    public function testEmptyQueueReturnsSuccess(): void
    {
        $tester = new CommandTester($this->makeCommand([]));
        $code   = $tester->execute([]);

        self::assertSame(Command::SUCCESS, $code);
        self::assertStringContainsString('Aucun élément en attente', $tester->getDisplay());
    }

    public function testMissingInputFileMarksItemAsFailed(): void
    {
        $item = new PdfQueue();
        $item->setToken('test_token');
        $item->setInputFiles(['missing_file.pdf']);
        $item->setCreatedAt(new \DateTimeImmutable());

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->atLeastOnce())->method('flush');

        $tester = new CommandTester($this->makeCommand([$item], em: $em, projectDir: '/tmp'));
        $code   = $tester->execute([]);

        self::assertSame(Command::SUCCESS, $code);
        self::assertSame('failed', $item->getStatus(), 'Un fichier manquant doit marquer l\'item comme "failed"');
        self::assertNotNull($item->getProcessedAt());
    }

    public function testSuccessfulProcessingMarksItemAsDone(): void
    {
        // Créer le fichier à l'emplacement attendu par la commande : {projectDir}/var/queue/{token}/
        $projectDir = sys_get_temp_dir();
        $tmpDir     = $projectDir . '/var/queue/test_token/';
        @mkdir($tmpDir, 0755, true);
        file_put_contents($tmpDir . '1_doc.pdf', '%PDF-1.4 fake content');

        $item = new PdfQueue();
        $item->setToken('test_token');
        $item->setInputFiles(['1_doc.pdf']);
        $item->setCreatedAt(new \DateTimeImmutable());

        $pdfService = $this->createMock(YourGotenbergService::class);
        $pdfService->method('generatePdfFromMerge')->willReturn('%PDF-1.4 merged content');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->atLeastOnce())->method('flush');
        $em->expects($this->once())->method('persist');

        $tester = new CommandTester($this->makeCommand([$item], $pdfService, $em, $projectDir));
        $code   = $tester->execute([]);

        // Nettoyage
        @unlink($tmpDir . '1_doc.pdf');
        @rmdir($tmpDir);
        if ($item->getResultFile()) {
            @unlink($projectDir . '/var/pdfs/' . $item->getResultFile());
        }

        self::assertSame(Command::SUCCESS, $code);
        self::assertSame('done', $item->getStatus(), 'Un item traité avec succès doit avoir le statut "done"');
        self::assertNotNull($item->getResultFile(), 'Un item done doit avoir un fichier résultat');
        self::assertNotNull($item->getProcessedAt());
    }

    public function testLimitOptionIsRespected(): void
    {
        $repo = $this->createMock(PdfQueueRepository::class);
        $repo->expects($this->once())
            ->method('findPending')
            ->with(3)
            ->willReturn([]);

        $command = new HandleQueueCommand(
            $repo,
            $this->createMock(YourGotenbergService::class),
            $this->createMock(EntityManagerInterface::class),
            '/tmp',
        );

        $tester = new CommandTester($command);
        $tester->execute(['--limit' => 3]);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
    }

    public function testMultipleItemsAreAllProcessed(): void
    {
        $item1 = new PdfQueue();
        $item1->setToken('token1');
        $item1->setInputFiles(['missing1.pdf']);
        $item1->setCreatedAt(new \DateTimeImmutable());

        $item2 = new PdfQueue();
        $item2->setToken('token2');
        $item2->setInputFiles(['missing2.pdf']);
        $item2->setCreatedAt(new \DateTimeImmutable());

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->atLeastOnce())->method('flush');

        $tester = new CommandTester($this->makeCommand([$item1, $item2], em: $em));
        $tester->execute([]);

        self::assertSame('failed', $item1->getStatus(), 'Item 1 doit être traité');
        self::assertSame('failed', $item2->getStatus(), 'Item 2 doit être traité');
    }
}
