<?php

namespace App\Controller;

use App\Repository\SweatShirtRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(Request $request, SweatShirtRepository $sweatShirtRepository): Response
    {
        // Fourchette de prix choisie dans le filtre (10-29, 29-35 ou 35-50)
        $fourchette = $request->query->get('prix');

        $sweatShirts = match ($fourchette) {
            '10-29' => $sweatShirtRepository->findByPriceRange(10, 29),
            '29-35' => $sweatShirtRepository->findByPriceRange(29, 35),
            '35-50' => $sweatShirtRepository->findByPriceRange(35, 50),
            default => $sweatShirtRepository->findAll(),
        };

        return $this->render('product/index.html.twig', [
            'sweatShirts' => $sweatShirts,
            'fourchette' => $fourchette,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(int $id, SweatShirtRepository $sweatShirtRepository): Response
    {
        $sweatShirt = $sweatShirtRepository->find($id);

        if (!$sweatShirt) {
            throw $this->createNotFoundException('Sweat-shirt introuvable.');
        }

        return $this->render('product/show.html.twig', [
            'sweatShirt' => $sweatShirt,
        ]);
    }
}
