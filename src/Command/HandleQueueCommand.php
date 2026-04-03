<?php

namespace App\Command;

use App\Entity\Generation;
use App\Repository\PdfQueueRepository;
use App\Service\YourGotenbergService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:handle-queue',
    description: 'Traite les éléments en attente de la file de merge PDF',
)]
class HandleQueueCommand extends Command
{
    public function __construct(
        private PdfQueueRepository $queueRepository,
        private YourGotenbergService $pdfService,
        private EntityManagerInterface $em,
        private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Nombre max d\'éléments à traiter', 5);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');

        $items = $this->queueRepository->findPending($limit);

        if (empty($items)) {
            $io->success('Aucun élément en attente.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Traitement de %d élément(s)...', count($items)));

        $pdfsDir   = $this->projectDir . '/var/pdfs/';
        $queueDir  = $this->projectDir . '/var/queue/';

        if (!is_dir($pdfsDir)) {
            mkdir($pdfsDir, 0755, true);
        }

        foreach ($items as $item) {
            $item->setStatus('processing');
            $this->em->flush();

            try {
                $dir       = $queueDir . $item->getToken() . '/';
                $contents  = [];
                $filenames = [];

                foreach ($item->getInputFiles() as $filename) {
                    $path = $dir . $filename;
                    if (!file_exists($path)) {
                        throw new \RuntimeException("Fichier introuvable : {$path}");
                    }
                    $contents[]  = file_get_contents($path);
                    $filenames[] = $filename;
                }

                $pdfContent = $this->pdfService->generatePdfFromMerge($contents, $filenames);

                $resultFile = 'merged_' . uniqid('', true) . '.pdf';
                file_put_contents($pdfsDir . $resultFile, $pdfContent);

                $item->setStatus('done');
                $item->setResultFile($resultFile);
                $item->setProcessedAt(new \DateTimeImmutable());

                // Enregistrer dans l'historique
                $generation = new Generation();
                $generation->setUser($item->getUser());
                $generation->setFile($resultFile);
                $generation->setToolName('Merge PDF');
                $generation->setCreateadAt(new \DateTimeImmutable());
                $this->em->persist($generation);

                $io->writeln(" [OK] Item #{$item->getId()} traité → {$resultFile}");

            } catch (\Throwable $e) {
                $item->setStatus('failed');
                $item->setProcessedAt(new \DateTimeImmutable());
                $io->error(" [FAIL] Item #{$item->getId()} : " . $e->getMessage());
            }

            $this->em->flush();
        }

        $io->success('File traitée.');

        // Crontab (toutes les 10 min) :
        // */10 * * * * docker exec symfony-web-2026 php /var/www/html/WR602d/bin/console app:handle-queue

        return Command::SUCCESS;
    }
}
