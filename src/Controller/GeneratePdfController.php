<?php

namespace App\Controller;

use App\Repository\ToolRepository;
use App\Security\Voter\ToolAccessVoter;
use App\Service\YourGotenbergService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\File;

#[IsGranted('ROLE_USER')]
class GeneratePdfController extends AbstractController
{
    public function __construct(
        private YourGotenbergService $pdfService,
        private ToolRepository $toolRepository,
    ) {
    }

    /** Hub : page de sélection des outils */
    #[Route('/generate-pdf', name: 'app_generate_pdf', methods: ['GET'])]
    public function hub(): Response
    {
        $allTools = $this->toolRepository->findAll();
        $routeMap = [
            'url to pdf'  => '/convert/url',
            'html to pdf' => '/html-to-pdf',
        ];

        $toolsData = [];
        foreach ($allTools as $tool) {
            $key = strtolower($tool->getName());
            $toolsData[] = [
                'name'      => $tool->getName(),
                'color'     => $tool->getColor() ?? '#FF701F',
                'icon'      => $tool->getIcon() ?? 'fa-solid fa-file-pdf',
                'isActive'  => $tool->isActive(),
                'hasAccess' => $this->isGranted(ToolAccessVoter::ACCESS, $tool),
                'route'     => $routeMap[$key] ?? null,
            ];
        }

        return $this->render('pdf/hub.html.twig', [
            'toolsData' => $toolsData,
        ]);
    }

    /** Formulaire URL → PDF */
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
            $pdfContent = $this->pdfService->generatePdfFromUrl($form->getData()['url']);

            return new Response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="generated.pdf"',
            ]);
        }

        return $this->render('pdf/generate_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /** Formulaire HTML → PDF */
    #[Route('/html-to-pdf', name: 'app_html_to_pdf', methods: ['GET', 'POST'])]
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
            $htmlContent = $form->getData()['htmlFile']->getContent();
            $pdfContent  = $this->pdfService->generatePdfFromHtml($htmlContent);

            return new Response($pdfContent, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="generated.pdf"',
            ]);
        }

        return $this->render('pdf/html_to_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
