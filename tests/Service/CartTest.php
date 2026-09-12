<?php

namespace App\Tests\Service;

use App\Entity\SweatShirt;
use App\Repository\SweatShirtRepository;
use App\Service\Cart;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CartTest extends TestCase
{
    // Cree un panier pret a l'emploi pour les tests, avec un faux depot de sweat-shirts
    private function creerPanier(SweatShirtRepository $sweatShirtRepository): Cart
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $requestStack = new RequestStack();
        $requestStack->push($request);

        return new Cart($requestStack, $sweatShirtRepository);
    }

    private function creerFauxSweatShirt(int $id, string $nom, float $prix): SweatShirt
    {
        $sweatShirt = new SweatShirt();
        $sweatShirt->setName($nom);
        $sweatShirt->setPrice($prix);
        $sweatShirt->setImageFilename('exemple.jpg');
        $sweatShirt->setIsFeatured(false);
        $sweatShirt->setStockXS(10);
        $sweatShirt->setStockS(10);
        $sweatShirt->setStockM(10);
        $sweatShirt->setStockL(10);
        $sweatShirt->setStockXL(10);

        // On force l'id via la reflexion car il est normalement genere par la base de donnees
        $reflection = new \ReflectionProperty(SweatShirt::class, 'id');
        $reflection->setValue($sweatShirt, $id);

        return $sweatShirt;
    }

    public function testAjouterUnArticleAuPanier(): void
    {
        $blackbelt = $this->creerFauxSweatShirt(1, 'Blackbelt', 29.90);

        $sweatShirtRepository = $this->createMock(SweatShirtRepository::class);
        $sweatShirtRepository->method('find')->willReturn($blackbelt);

        $panier = $this->creerPanier($sweatShirtRepository);
        $panier->add(1, 'M');

        $lignes = $panier->getItems();

        $this->assertCount(1, $lignes);
        $this->assertSame('M', $lignes[0]['size']);
        $this->assertSame(1, $lignes[0]['quantity']);
        $this->assertEqualsWithDelta(29.90, $lignes[0]['sousTotal'], 0.001);
    }

    public function testAjouterDeuxFoisLeMemeArticleAugmenteLaQuantite(): void
    {
        $blackbelt = $this->creerFauxSweatShirt(1, 'Blackbelt', 29.90);

        $sweatShirtRepository = $this->createMock(SweatShirtRepository::class);
        $sweatShirtRepository->method('find')->willReturn($blackbelt);

        $panier = $this->creerPanier($sweatShirtRepository);
        $panier->add(1, 'M');
        $panier->add(1, 'M');

        $lignes = $panier->getItems();

        $this->assertCount(1, $lignes);
        $this->assertSame(2, $lignes[0]['quantity']);
    }

    public function testRetirerUnArticleDuPanier(): void
    {
        $blackbelt = $this->creerFauxSweatShirt(1, 'Blackbelt', 29.90);

        $sweatShirtRepository = $this->createMock(SweatShirtRepository::class);
        $sweatShirtRepository->method('find')->willReturn($blackbelt);

        $panier = $this->creerPanier($sweatShirtRepository);
        $panier->add(1, 'M');
        $panier->remove('1-M');

        $this->assertCount(0, $panier->getItems());
    }

    public function testLeTotalAdditionnePlusieursArticles(): void
    {
        $blackbelt = $this->creerFauxSweatShirt(1, 'Blackbelt', 29.90);
        $pokeball = $this->creerFauxSweatShirt(4, 'Pokeball', 45);

        $sweatShirtRepository = $this->createMock(SweatShirtRepository::class);
        $sweatShirtRepository->method('find')->willReturnMap([
            [1, $blackbelt],
            [4, $pokeball],
        ]);

        $panier = $this->creerPanier($sweatShirtRepository);
        $panier->add(1, 'M');
        $panier->add(4, 'L');

        $this->assertEqualsWithDelta(74.90, $panier->getTotal(), 0.001);
    }
}
