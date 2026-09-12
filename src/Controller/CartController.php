<?php

namespace App\Controller;

use App\Service\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(Cart $cart): Response
    {
        return $this->render('cart/index.html.twig', [
            'lignes' => $cart->getItems(),
            'total' => $cart->getTotal(),
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(int $id, Request $request, Cart $cart, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {
        $token = $request->request->get('_csrf_token');

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('cart_add', $token))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $taille = $request->request->get('taille');
        $cart->add($id, $taille);

        $this->addFlash('success', 'Le sweat-shirt a bien ete ajoute au panier.');

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{cle}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(string $cle, Request $request, Cart $cart, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {
        $token = $request->request->get('_csrf_token');

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('cart_remove', $token))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $cart->remove($cle);

        return $this->redirectToRoute('app_cart');
    }
}
