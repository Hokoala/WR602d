<?php

namespace App\Controller;

use App\Service\YourGotenbergService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;

class GeneratePdfController extends AbstractController
{
    public function __construct(
        private YourGotenbergService $pdfService,
    ) {
    }

    #[Route('/generate-pdf', name: 'app_generate_pdf', methods: ['GET', 'POST'])]
    public function generatePdf(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'URL' => 'url',
                    'Fichier HTML' => 'html',
                ],
                'label' => 'Type de conversion',
            ])
            ->add('url', null, ['required' => false, 'label' => 'URL'])
            ->add('htmlFile', FileType::class, [
                'required' => false,
                'label' => 'Fichier HTML',
                'constraints' => [
                    new File(['mimeTypes' => ['text/html'], 'mimeTypesMessage' => 'Veuillez envoyer un fichier HTML valide.']),
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($data['type'] === 'html') {
                $htmlContent = $data['htmlFile']->getContent();
                $pdfContent = $this->pdfService->generatePdfFromHtml($htmlContent);
            } else {
                $pdfContent = $this->pdfService->generatePdfFromUrl($data['url']);
            }

            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="generated.pdf"',
            ]);
        }

        return $this->render('pdf/generate_pdf.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
