<?php

namespace App\Service;

use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

// Service qui utilise Stripe pour simuler le reglement d'une commande (mode test/sandbox)
class StripeService
{
    public function __construct(
        #[Autowire(env: 'STRIPE_SECRET_KEY')]
        private string $stripeSecretKey,
    ) {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    // Cree une session de paiement Stripe a partir des lignes du panier
    // et renvoie l'url vers laquelle rediriger le client pour payer
    public function creerSessionPaiement(array $lignesPanier, string $successUrl, string $cancelUrl): Session
    {
        $lineItems = [];

        foreach ($lignesPanier as $ligne) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $ligne['sweatShirt']->getName() . ' (taille ' . $ligne['size'] . ')',
                    ],
                    // Stripe attend un montant en centimes
                    'unit_amount' => (int) round($ligne['sweatShirt']->getPrice() * 100),
                ],
                'quantity' => $ligne['quantity'],
            ];
        }

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }
}
