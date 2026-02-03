<?php

namespace App\DataFixtures;

use App\Entity\Plan;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $plans = [
            [
                'name' => 'FREE',
                'description' => 'Plan gratuit pour découvrir le service. Accès limité aux fonctionnalités de base.',
                'limitGeneration' => 10,
                'image' => 'free.png',
                'role' => 'ROLE_FREE',
                'price' => '0.00',
                'specialPrice' => null,
                'specialPriceFrom' => null,
                'specialPriceTo' => null,
                'active' => true,
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

            $manager->persist($plan);
        }

        $manager->flush();
    }
}
