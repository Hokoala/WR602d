<?php

namespace App\DataFixtures;

use App\Entity\Plan;
use App\Entity\Tool;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create Tools
        $tools = [
            [
                'name' => 'URL to PDF',
                'icon' => 'fa-solid fa-link',
                'description' => 'Convertir une URL en fichier PDF.',
                'color' => '#FF701F',
                'isActive' => true,
            ],
            [
                'name' => 'HTML to PDF',
                'icon' => 'fa-solid fa-code',
                'description' => 'Convertir du code HTML en fichier PDF.',
                'color' => '#3B82F6',
                'isActive' => true,
            ],
            [
                'name' => 'Merge PDF',
                'icon' => 'fa-solid fa-object-group',
                'description' => 'Fusionner plusieurs fichiers PDF en un seul.',
                'color' => '#10B981',
                'isActive' => true,
            ],
            [
                'name' => 'Split PDF',
                'icon' => 'fa-solid fa-scissors',
                'description' => 'Diviser un fichier PDF en plusieurs pages.',
                'color' => '#8B5CF6',
                'isActive' => false,
            ],
            [
                'name' => 'Compress PDF',
                'icon' => 'fa-solid fa-compress',
                'description' => 'Compresser un fichier PDF pour réduire sa taille.',
                'color' => '#F59E0B',
                'isActive' => false,
            ],
        ];

        $toolEntities = [];
        foreach ($tools as $toolData) {
            $tool = new Tool();
            $tool->setName($toolData['name']);
            $tool->setIcon($toolData['icon']);
            $tool->setDescription($toolData['description']);
            $tool->setColor($toolData['color']);
            $tool->setIsActive($toolData['isActive']);

            $manager->persist($tool);
            $toolEntities[$toolData['name']] = $tool;
        }

        // Create Plans
        $plans = [
            [
                'name' => 'FREE',
                'description' => 'Plan gratuit pour découvrir le service. Accès à tous les outils, limité à 5 générations.',
                'limitGeneration' => 5,
                'image' => 'free.png',
                'role' => 'ROLE_FREE',
                'price' => '0.00',
                'specialPrice' => null,
                'specialPriceFrom' => null,
                'specialPriceTo' => null,
                'active' => true,
                'tools' => ['URL to PDF', 'HTML to PDF', 'Merge PDF', 'Split PDF', 'Compress PDF'],
            ],
            [
                'name' => 'BASIC',
                'description' => 'Plan standard avec plus de générations et fonctionnalités avancées.',
                'limitGeneration' => 100,
                'image' => 'basic.png',
                'role' => 'ROLE_BASIC',
                'price' => '9.99',
                'specialPrice' => '7.99',
                'specialPriceFrom' => new \DateTime('2024-01-01'),
                'specialPriceTo' => new \DateTime('2024-12-31'),
                'active' => true,
                'tools' => ['URL to PDF', 'HTML to PDF', 'Merge PDF', 'Split PDF', 'Compress PDF'],
            ],
            [
                'name' => 'PREMIUM',
                'description' => 'Plan premium avec générations illimitées et accès prioritaire au support.',
                'limitGeneration' => -1,
                'image' => 'premium.png',
                'role' => 'ROLE_PREMIUM',
                'price' => '29.99',
                'specialPrice' => '24.99',
                'specialPriceFrom' => new \DateTime('2024-01-01'),
                'specialPriceTo' => new \DateTime('2024-12-31'),
                'active' => true,
                'tools' => ['URL to PDF', 'HTML to PDF', 'Merge PDF', 'Split PDF', 'Compress PDF'],
            ],
        ];

        foreach ($plans as $planData) {
            $plan = new Plan();
            $plan->setName($planData['name']);
            $plan->setDescription($planData['description']);
            $plan->setLimitGeneration($planData['limitGeneration']);
            $plan->setImage($planData['image']);
            $plan->setRole($planData['role']);
            $plan->setPrice($planData['price']);
            $plan->setSpecialPrice($planData['specialPrice']);
            $plan->setSpecialPriceFrom($planData['specialPriceFrom']);
            $plan->setSpecialPriceTo($planData['specialPriceTo']);
            $plan->setActive($planData['active']);
            $plan->setCreatedAt(new \DateTimeImmutable());

            // Associate tools with plan
            foreach ($planData['tools'] as $toolName) {
                $plan->addTool($toolEntities[$toolName]);
            }

            $manager->persist($plan);
        }

        $manager->flush();
    }
}
