<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function index(): Response
    {
        $items = [
            ['title' => 'Symfony', 'description' => 'Framework PHP pour les applications web'],
            ['title' => 'Twig', 'description' => 'Moteur de templates flexible et rapide'],
            ['title' => 'Doctrine', 'description' => 'ORM pour la gestion de base de données'],
        ];

        return $this->render('test/index.html.twig', [
            'pageTitle' => 'Page de Test',
            'items' => $items,
        ]);
    }
}
