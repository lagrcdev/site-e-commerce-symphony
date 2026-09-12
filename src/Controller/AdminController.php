<?php

namespace App\Controller;

use App\Entity\SweatShirt;
use App\Repository\SweatShirtRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(SweatShirtRepository $sweatShirtRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'sweatShirts' => $sweatShirtRepository->findAll(),
        ]);
    }

    #[Route('/admin/add', name: 'app_admin_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('admin_add', $request->request->get('_csrf_token')))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $sweatShirt = new SweatShirt();
        $sweatShirt->setName($request->request->get('name'));
        $sweatShirt->setPrice((float) $request->request->get('price'));
        $sweatShirt->setImageFilename($request->request->get('imageFilename'));
        $sweatShirt->setIsFeatured(false);
        $sweatShirt->setStockXS((int) $request->request->get('stockXS'));
        $sweatShirt->setStockS((int) $request->request->get('stockS'));
        $sweatShirt->setStockM((int) $request->request->get('stockM'));
        $sweatShirt->setStockL((int) $request->request->get('stockL'));
        $sweatShirt->setStockXL((int) $request->request->get('stockXL'));

        $entityManager->persist($sweatShirt);
        $entityManager->flush();

        $this->addFlash('success', 'Le sweat-shirt a bien ete ajoute.');

        return $this->redirectToRoute('app_admin');
    }

    #[Route('/admin/edit/{id}', name: 'app_admin_edit', methods: ['POST'])]
    public function edit(int $id, Request $request, SweatShirtRepository $sweatShirtRepository, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('admin_edit', $request->request->get('_csrf_token')))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $sweatShirt = $sweatShirtRepository->find($id);

        if (!$sweatShirt) {
            throw $this->createNotFoundException('Sweat-shirt introuvable.');
        }

        $sweatShirt->setName($request->request->get('name'));
        $sweatShirt->setPrice((float) $request->request->get('price'));
        $sweatShirt->setImageFilename($request->request->get('imageFilename'));
        $sweatShirt->setIsFeatured($request->request->getBoolean('isFeatured'));
        $sweatShirt->setStockXS((int) $request->request->get('stockXS'));
        $sweatShirt->setStockS((int) $request->request->get('stockS'));
        $sweatShirt->setStockM((int) $request->request->get('stockM'));
        $sweatShirt->setStockL((int) $request->request->get('stockL'));
        $sweatShirt->setStockXL((int) $request->request->get('stockXL'));

        $entityManager->flush();

        $this->addFlash('success', 'Le sweat-shirt a bien ete modifie.');

        return $this->redirectToRoute('app_admin');
    }

    #[Route('/admin/delete/{id}', name: 'app_admin_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, SweatShirtRepository $sweatShirtRepository, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): RedirectResponse
    {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('admin_delete', $request->request->get('_csrf_token')))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $sweatShirt = $sweatShirtRepository->find($id);

        if (!$sweatShirt) {
            throw $this->createNotFoundException('Sweat-shirt introuvable.');
        }

        $entityManager->remove($sweatShirt);
        $entityManager->flush();

        $this->addFlash('success', 'Le sweat-shirt a bien ete supprime.');

        return $this->redirectToRoute('app_admin');
    }
}
