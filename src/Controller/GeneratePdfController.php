<?php

namespace App\Controller;

use App\Entity\Generation;
use App\Entity\User;
use App\Repository\GenerationRepository;
use App\Repository\ToolRepository;
use App\Security\Voter\ToolAccessVoter;
use App\Service\YourGotenbergService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

#[IsGranted('ROLE_USER')]
class GeneratePdfController extends AbstractController
{
    public function __construct(
        private YourGotenbergService $pdfService,
        private ToolRepository $toolRepository,
        private GenerationRepository $generationRepository,
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
    ) {
    }

    private function sendPdfByEmail(string $fileContent, string $filename, string $toolName, string $mimeType = 'application/pdf'): void
    {
        /** @var User $user */
        $user = $this->getUser();
        $displayName = trim(($user->getFirstname() ?? '') . ' ' . ($user->getLastname() ?? '')) ?: $user->getEmail();

        $email = (new Email())
            ->from('noreply@docly.com')
            ->to($user->getEmail())
            ->subject("Votre fichier généré avec Docly — {$toolName}")
            ->text("Bonjour {$displayName},\n\nVotre fichier généré avec l'outil \"{$toolName}\" est disponible en pièce jointe.\n\nL'équipe Docly")
            ->attach($fileContent, $filename, $mimeType);

        try {
            $this->mailer->send($email);
        } catch (\Throwable) {
            // Ne pas bloquer le téléchargement si l'envoi échoue
        }
    }

    private function hasReachedGenerationLimit(): bool
    {
        /** @var User $user */
        $user  = $this->getUser();
        $limit = $user->getPlan()?->getLimitGeneration();

        if ($limit === null || $limit === -1) {
            return false;
        }

        return $this->generationRepository->countByUserToday($user) >= $limit;
    }

    private function denyIfLimitReached(string $route): ?Response
    {
        if (!$this->hasReachedGenerationLimit()) {
            return null;
        }

        /** @var User $user */
        $user     = $this->getUser();
        $limit    = $user->getPlan()?->getLimitGeneration();
        $planName = $user->getPlan()?->getName() ?? 'FREE';

        $this->addFlash(
            'warning',
            "Vous avez atteint la limite de {$limit} générations ce mois-ci (plan {$planName}). "
            . "Le compteur se réinitialise dans 24h."
        );

        return $this->redirectToRoute($route);
    }

    private function saveGeneration(string $filename, string $toolName): void
    {
        /** @var User $user */
        $user = $this->getUser();
        $generation = new Generation();
        $generation->setUser($user);
        $generation->setFile($filename);
        $generation->setToolName($toolName);
        $generation->setCreateadAt(new \DateTimeImmutable());
        $this->em->persist($generation);
        $this->em->flush();
    }

    /** Hub :page de sélection des outils */
    #[Route('/convert', name: 'app_convert', methods: ['GET'])]
    public function hub(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $allTools = $this->toolRepository->findAll();
        $routeMap = [
            'url to pdf'        => '/convert/url',
            'html to pdf'       => '/convert/html',
            'merge pdf'         => '/convert/merge',
            'markdown to pdf'   => '/convert/markdown',
            'office to pdf'     => '/convert/office',
            'screenshot to pdf' => '/convert/screenshot',
            'split pdf'         => '/convert/split',
            'compress pdf'      => '/convert/compress',
            'image to pdf'      => '/convert/image',
            'wysiwyg to pdf'    => '/convert/wysiwyg',
        ];

        $toolsData = [];
        foreach ($allTools as $tool) {
            $key = strtolower($tool->getName());
            $toolsData[] = [
                'name'        => $tool->getName(),
                'description' => $tool->getDescription(),
                'color'       => $tool->getColor() ?? '#FF701F',
                'icon'        => $tool->getIcon() ?? 'fa-solid fa-file-pdf',
                'isActive'    => $tool->isActive(),
                'hasAccess'   => $this->isGranted(ToolAccessVoter::ACCESS, $tool),
                'route'       => $routeMap[$key] ?? null,
            ];
        }

        $limit = $user->getPlan()?->getLimitGeneration();
        $used  = $this->generationRepository->countByUserToday($user);

        return $this->render('pdf/hub.html.twig', [
            'toolsData'       => $toolsData,
            'generationUsed'  => $used,
            'generationLimit' => $limit,
            'planName'        => $user->getPlan()?->getName() ?? 'FREE',
        ]);
    }

    /** URL → PDF */
    #[Route('/convert/url', name: 'app_convert_url', methods: ['GET', 'POST'])]
    public function generatePdf(Request $request): Response
    {
        $tool = $this->toolRepository->findByNameKeyword('url to pdf');
        if ($tool) {
            $this->denyAccessUnlessGranted(ToolAccessVoter::ACCESS, $tool);
        }

        $form = $this->createFormBuilder()
            ->add('url', null, ['required' => true, 'label' => 'URL'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($redirect = $this->denyIfLimitReached('app_convert_url')) {
                return $redirect;
            }

            $pdfContent = $this->pdfService->generatePdfFromUrl($form->getData()['url']);
            $this->saveGeneration('generated.pdf', 'URL to PDF');
            $this->sendPdfByEmail($pdfContent, 'generated.pdf', 'URL to PDF');

            return new Response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="generated.pdf"',
            ]);
        }

        return $this->render('pdf/generate_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /** HTML → PDF */
    #[Route('/convert/html', name: 'app_convert_html', methods: ['GET', 'POST'])]
    public function htmlToPdf(Request $request): Response
    {
        $tool = $this->toolRepository->findByNameKeyword('html to pdf');
        if ($tool) {
            $this->denyAccessUnlessGranted(ToolAccessVoter::ACCESS, $tool);
        }

        $form = $this->createFormBuilder()
            ->add('htmlFile', FileType::class, [
                'required'    => true,
                'label'       => 'Fichier HTML',
                'constraints' => [
                    new File(['mimeTypes' => ['text/html'], 'mimeTypesMessage' => 'Veuillez envoyer un fichier HTML valide.']),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($redirect = $this->denyIfLimitReached('app_html_to_pdf')) {
                return $redirect;
            }

            $htmlContent = $form->getData()['htmlFile']->getContent();
            $pdfContent  = $this->pdfService->generatePdfFromHtml($htmlContent);
            $this->saveGeneration('generated.pdf', 'HTML to PDF');
            $this->sendPdfByEmail($pdfContent, 'generated.pdf', 'HTML to PDF');

            return new Response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="generated.pdf"',
            ]);
        }

        return $this->render('pdf/html_to_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /** Merge PDF */
    #[Route('/convert/merge', name: 'app_convert_merge', methods: ['GET', 'POST'])]
    public function mergePdf(Request $request): Response
    {
        $tool = $this->toolRepository->findByNameKeyword('merge pdf');
        if ($tool) {
            $this->denyAccessUnlessGranted(ToolAccessVoter::ACCESS, $tool);
        }

        $form = $this->createFormBuilder()
            ->add('pdfFiles', FileType::class, [
                'required'    => true,
                'label'       => 'Fichiers PDF',
                'multiple'    => true,
                'constraints' => [
                    new All([
                        new File(['mimeTypes' => ['application/pdf'], 'mimeTypesMessage' => 'Veuillez envoyer des fichiers PDF valides.']),
                    ]),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($redirect = $this->denyIfLimitReached('app_merge_pdf')) {
                return $redirect;
            }

            $files     = $form->getData()['pdfFiles'];
            $contents  = array_map(fn($f) => $f->getContent(), $files);
            $filenames = array_map(fn($f) => $f->getClientOriginalName(), $files);
            $pdfContent = $this->pdfService->generatePdfFromMerge($contents, $filenames);
            $this->saveGeneration('merged.pdf', 'Merge PDF');
            $this->sendPdfByEmail($pdfContent, 'merged.pdf', 'Merge PDF');

            return new Response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="merged.pdf"',
            ]);
        }

        return $this->render('pdf/merge_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /** Markdown → PDF */
    #[Route('/convert/markdown', name: 'app_convert_markdown', methods: ['GET', 'POST'])]
    public function markdownToPdf(Request $request): Response
    {
        $tool = $this->toolRepository->findByNameKeyword('markdown to pdf');
        if ($tool) {
            $this->denyAccessUnlessGranted(ToolAccessVoter::ACCESS, $tool);
        }

        $form = $this->createFormBuilder()
            ->add('mdFile', FileType::class, [
                'required'    => true,
                'label'       => 'Fichier Markdown',
                'constraints' => [
                    new File(['mimeTypes' => ['text/plain', 'text/markdown'], 'mimeTypesMessage' => 'Veuillez envoyer un fichier Markdown valide.']),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($redirect = $this->denyIfLimitReached('app_markdown_to_pdf')) {
                return $redirect;
            }

            $mdContent  = $form->getData()['mdFile']->getContent();
            $pdfContent = $this->pdfService->generatePdfFromMarkdown($mdContent);
            $this->saveGeneration('generated.pdf', 'Markdown to PDF');
            $this->sendPdfByEmail($pdfContent, 'generated.pdf', 'Markdown to PDF');

            return new Response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="generated.pdf"',
            ]);
        }

        return $this->render('pdf/markdown_to_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /** Office → PDF */
    #[Route('/convert/office', name: 'app_convert_office', methods: ['GET', 'POST'])]
    public function officeToPdf(Request $request): Response
    {
        $tool = $this->toolRepository->findByNameKeyword('office to pdf');
        if ($tool) {
            $this->denyAccessUnlessGranted(ToolAccessVoter::ACCESS, $tool);
        }

        $form = $this->createFormBuilder()
            ->add('officeFile', FileType::class, [
                'required' => true,
                'label'    => 'Fichier Office',
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'application/msword',
                            'application/vnd.ms-excel',
                            'application/vnd.ms-powerpoint',
                        ],
                        'mimeTypesMessage' => 'Veuillez envoyer un fichier Word, Excel ou PowerPoint.',
                    ]),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($redirect = $this->denyIfLimitReached('app_office_to_pdf')) {
                return $redirect;
            }

            $file       = $form->getData()['officeFile'];
            $pdfContent = $this->pdfService->generatePdfFromOffice($file->getContent(), $file->getClientOriginalName());
            $this->saveGeneration('generated.pdf', 'Office to PDF');
            $this->sendPdfByEmail($pdfContent, 'generated.pdf', 'Office to PDF');

            return new Response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="generated.pdf"',
            ]);
        }

        return $this->render('pdf/office_to_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /** Screenshot → PDF */
    #[Route('/convert/screenshot', name: 'app_convert_screenshot', methods: ['GET', 'POST'])]
    public function screenshotToPdf(Request $request): Response
    {
        $tool = $this->toolRepository->findByNameKeyword('screenshot to pdf');
        if ($tool) {
            $this->denyAccessUnlessGranted(ToolAccessVoter::ACCESS, $tool);
        }

        $form = $this->createFormBuilder()
            ->add('url', null, ['required' => true, 'label' => 'URL'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($redirect = $this->denyIfLimitReached('app_screenshot_to_pdf')) {
                return $redirect;
            }

            $pdfContent = $this->pdfService->generateScreenshotFromUrl($form->getData()['url']);
            $this->saveGeneration('screenshot.pdf', 'Screenshot to PDF');
            $this->sendPdfByEmail($pdfContent, 'screenshot.pdf', 'Screenshot to PDF');

            return new Response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="screenshot.pdf"',
            ]);
        }

        return $this->render('pdf/screenshot_to_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /** Split PDF */
    #[Route('/convert/split', name: 'app_split_pdf', methods: ['GET', 'POST'])]
    public function splitPdf(Request $request): Response
    {
        $tool = $this->toolRepository->findByNameKeyword('split pdf');
        if ($tool) {
            $this->denyAccessUnlessGranted(ToolAccessVoter::ACCESS, $tool);
        }

        $form = $this->createFormBuilder()
            ->add('pdfFile', FileType::class, [
                'required'    => true,
                'label'       => 'Fichier PDF',
                'constraints' => [
                    new File(['mimeTypes' => ['application/pdf'], 'mimeTypesMessage' => 'Veuillez envoyer un fichier PDF valide.']),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($redirect = $this->denyIfLimitReached('app_split_pdf')) {
                return $redirect;
            }

            $file       = $form->getData()['pdfFile'];
            $zipContent = $this->pdfService->splitPdf($file->getContent(), $file->getClientOriginalName());
            $this->saveGeneration('split_pages.zip', 'Split PDF');
            $this->sendPdfByEmail($zipContent, 'split_pages.zip', 'Split PDF', 'application/zip');

            return new Response($zipContent, 200, [
                'Content-Type'        => 'application/zip',
                'Content-Disposition' => 'attachment; filename="split_pages.zip"',
            ]);
        }

        return $this->render('pdf/split_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /** Compress PDF */
    #[Route('/convert/compress', name: 'app_compress_pdf', methods: ['GET', 'POST'])]
    public function compressPdf(Request $request): Response
    {
        $tool = $this->toolRepository->findByNameKeyword('compress pdf');
        if ($tool) {
            $this->denyAccessUnlessGranted(ToolAccessVoter::ACCESS, $tool);
        }

        $form = $this->createFormBuilder()
            ->add('pdfFile', FileType::class, [
                'required'    => true,
                'label'       => 'Fichier PDF',
                'constraints' => [
                    new File(['mimeTypes' => ['application/pdf'], 'mimeTypesMessage' => 'Veuillez envoyer un fichier PDF valide.']),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($redirect = $this->denyIfLimitReached('app_compress_pdf')) {
                return $redirect;
            }

            $file       = $form->getData()['pdfFile'];
            $pdfContent = $this->pdfService->compressPdf($file->getContent(), $file->getClientOriginalName());
            $this->saveGeneration('compressed.pdf', 'Compress PDF');
            $this->sendPdfByEmail($pdfContent, 'compressed.pdf', 'Compress PDF');

            return new Response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="compressed.pdf"',
            ]);
        }

        return $this->render('pdf/compress_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /** Image → PDF */
    #[Route('/convert/image', name: 'app_image_to_pdf', methods: ['GET', 'POST'])]
    public function imageToPdf(Request $request): Response
    {
        $tool = $this->toolRepository->findByNameKeyword('image to pdf');
        if ($tool) {
            $this->denyAccessUnlessGranted(ToolAccessVoter::ACCESS, $tool);
        }

        $form = $this->createFormBuilder()
            ->add('imageFile', FileType::class, [
                'required'    => true,
                'label'       => 'Image',
                'constraints' => [
                    new File([
                        'mimeTypes'        => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez envoyer une image JPG, PNG, GIF ou WebP.',
                    ]),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($redirect = $this->denyIfLimitReached('app_image_to_pdf')) {
                return $redirect;
            }

            $file       = $form->getData()['imageFile'];
            $pdfContent = $this->pdfService->imageToPdf($file->getContent(), $file->getMimeType());
            $this->saveGeneration('image.pdf', 'Image to PDF');
            $this->sendPdfByEmail($pdfContent, 'image.pdf', 'Image to PDF');

            return new Response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="image.pdf"',
            ]);
        }

        return $this->render('pdf/image_to_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /** WYSIWYG → PDF */
    #[Route('/convert/wysiwyg', name: 'app_convert_wysiwyg', methods: ['GET', 'POST'])]
    public function wysiwygToPdf(Request $request): Response
    {
        $tool = $this->toolRepository->findByNameKeyword('wysiwyg');
        if ($tool) {
            $this->denyAccessUnlessGranted(ToolAccessVoter::ACCESS, $tool);
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('wysiwyg-pdf', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('CSRF token invalide.');
            }

            if ($redirect = $this->denyIfLimitReached('app_convert_wysiwyg')) {
                return $redirect;
            }

            $htmlContent = $request->request->get('wysiwyg_content', '');
            if (empty(strip_tags($htmlContent))) {
                $this->addFlash('error', 'Le contenu ne peut pas être vide.');
                return $this->redirectToRoute('app_convert_wysiwyg');
            }

            $fullHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
                . 'body{font-family:Arial,sans-serif;padding:2cm;line-height:1.6;color:#111;}'
                . 'h1,h2,h3{margin:1rem 0 0.5rem;}'
                . 'img{max-width:100%;}'
                . 'ul,ol{padding-left:1.5rem;}'
                . 'blockquote{border-left:4px solid #e5e7eb;padding-left:1rem;color:#6b7280;}'
                . 'pre{background:#f3f4f6;padding:1rem;border-radius:0.5rem;overflow:auto;}'
                . '</style></head><body>' . $htmlContent . '</body></html>';

            $pdfContent = $this->pdfService->generatePdfFromHtml($fullHtml);
            $this->saveGeneration('wysiwyg.pdf', 'WYSIWYG to PDF');
            $this->sendPdfByEmail($pdfContent, 'wysiwyg.pdf', 'WYSIWYG to PDF');

            return new Response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="wysiwyg.pdf"',
            ]);
        }

        return $this->render('pdf/wysiwyg.html.twig');
    }
}
