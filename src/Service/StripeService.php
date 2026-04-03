<?php

namespace App\Service;

use App\Entity\Plan;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct(
        private string $secretKey,
        private string $webhookSecret,
        private EntityManagerInterface $em,
    ) {
        Stripe::setApiKey($this->secretKey);
    }

    /**
     * Crée ou réutilise un customer Stripe pour l'utilisateur.
     */
    private function getOrCreateCustomer(User $user): string
    {
        if ($user->getStripeCustomerId()) {
            return $user->getStripeCustomerId();
        }

        $customer = Customer::create([
            'email' => $user->getEmail(),
            'name'  => trim(($user->getFirstname() ?? '') . ' ' . ($user->getLastname() ?? '')),
            'metadata' => ['user_id' => $user->getId()],
        ]);

        $user->setStripeCustomerId($customer->id);
        $this->em->flush();

        return $customer->id;
    }

    /**
     * Crée une Checkout Session Stripe pour l'abonnement à un plan.
     * Retourne l'URL vers laquelle rediriger l'utilisateur.
     */
    public function createCheckoutSession(
        User $user,
        Plan $plan,
        string $successUrl,
        string $cancelUrl,
    ): string {
        $customerId = $this->getOrCreateCustomer($user);

        $session = Session::create([
            'mode'       => 'subscription',
            'customer'   => $customerId,
            'line_items' => [[
                'price'    => $plan->getStripePriceId(),
                'quantity' => 1,
            ]],
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => $cancelUrl,
            'metadata' => [
                'user_id' => $user->getId(),
                'plan_id' => $plan->getId(),
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => $user->getId(),
                    'plan_id' => $plan->getId(),
                ],
            ],
        ]);

        return $session->url;
    }

    /**
     * Vérifie la signature du webhook Stripe et retourne l'événement.
     */
    public function constructWebhookEvent(string $payload, string $sigHeader): \Stripe\Event
    {
        return Webhook::constructEvent($payload, $sigHeader, $this->webhookSecret);
    }
}
