<?php

namespace App\Tests\Entity;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\SweatShirt;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

// Teste la creation d'un achat (une commande payee avec ses articles)
class OrderTest extends TestCase
{
    public function testUnAchatContientLesBonsArticlesEtLeBonTotal(): void
    {
        $client = new User();
        $client->setName('client');
        $client->setEmail('client@exemple.com');
        $client->setDeliveryAddress('8 rue du bac, 54100 Nancy');

        $blackbelt = new SweatShirt();
        $blackbelt->setName('Blackbelt');
        $blackbelt->setPrice(29.90);

        $pokeball = new SweatShirt();
        $pokeball->setName('Pokeball');
        $pokeball->setPrice(45);

        $ligne1 = new OrderItem();
        $ligne1->setSweatShirt($blackbelt);
        $ligne1->setSize('M');
        $ligne1->setQuantity(1);
        $ligne1->setUnitPrice(29.90);

        $ligne2 = new OrderItem();
        $ligne2->setSweatShirt($pokeball);
        $ligne2->setSize('L');
        $ligne2->setQuantity(2);
        $ligne2->setUnitPrice(45);

        $commande = new Order();
        $commande->setUser($client);
        $commande->setCreatedAt(new \DateTimeImmutable());
        $commande->addOrderItem($ligne1);
        $commande->addOrderItem($ligne2);
        $commande->setTotal(29.90 + 2 * 45);
        $commande->setIsPaid(true);

        $this->assertTrue($commande->isPaid());
        $this->assertSame($client, $commande->getUser());
        $this->assertCount(2, $commande->getOrderItems());
        $this->assertEqualsWithDelta(119.90, $commande->getTotal(), 0.001);
    }
}
