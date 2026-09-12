<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Cart;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CheckoutController extends AbstractController
{
    // Redirige le client vers la page de paiement Stripe (mode test)
    #[Route('/checkout', name: 'app_checkout')]
    #[IsGranted('ROLE_USER')]
    public function index(Cart $cart, StripeService $stripeService): Response
    {
        $lignes = $cart->getItems();

        if (count($lignes) === 0) {
            $this->addFlash('danger', 'Votre panier est vide.');

            return $this->redirectToRoute('app_cart');
        }

        $session = $stripeService->creerSessionPaiement(
            $lignes,
            $this->generateUrl('app_checkout_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            $this->generateUrl('app_checkout_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        );

        return $this->redirect($session->url);
    }

    // Stripe redirige ici quand le paiement (de test) a reussi
    #[Route('/checkout/success', name: 'app_checkout_success')]
    #[IsGranted('ROLE_USER')]
    public function success(Cart $cart, EntityManagerInterface $entityManager): Response
    {
        $lignes = $cart->getItems();

        $order = new Order();
        $order->setUser($this->getUser());
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setIsPaid(true);
        $order->setTotal($cart->getTotal());

        foreach ($lignes as $ligne) {
            $orderItem = new OrderItem();
            $orderItem->setSweatShirt($ligne['sweatShirt']);
            $orderItem->setSize($ligne['size']);
            $orderItem->setQuantity($ligne['quantity']);
            $orderItem->setUnitPrice($ligne['sweatShirt']->getPrice());
            $order->addOrderItem($orderItem);
        }

        $entityManager->persist($order);
        $entityManager->flush();

        $cart->clear();

        return $this->render('checkout/success.html.twig', [
            'order' => $order,
        ]);
    }

    // Stripe redirige ici si le client annule le paiement
    #[Route('/checkout/cancel', name: 'app_checkout_cancel')]
    #[IsGranted('ROLE_USER')]
    public function cancel(): RedirectResponse
    {
        $this->addFlash('danger', 'Le paiement a ete annule.');

        return $this->redirectToRoute('app_cart');
    }
}
