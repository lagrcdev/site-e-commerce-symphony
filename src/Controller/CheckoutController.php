<?php

namespace App\Controller;

use App\Service\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CheckoutController extends AbstractController
{
    // Le paiement Stripe sera branche ici a l'etape suivante
    #[Route('/checkout', name: 'app_checkout')]
    public function index(Cart $cart): Response
    {
        return $this->render('checkout/index.html.twig', [
            'total' => $cart->getTotal(),
        ]);
    }
}
