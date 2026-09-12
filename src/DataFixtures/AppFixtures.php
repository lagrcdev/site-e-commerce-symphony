<?php

namespace App\DataFixtures;

use App\Entity\SweatShirt;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Les 10 sweat-shirts de la marque Stubborn (donnees fournies dans le brief)
        $sweatShirts = [
            ['name' => 'Blackbelt', 'price' => 29.90, 'image' => 'blackbelt.jpg', 'featured' => true],
            ['name' => 'BlueBelt', 'price' => 29.90, 'image' => 'bluebelt.jpg', 'featured' => false],
            ['name' => 'Street', 'price' => 34.50, 'image' => 'street.jpg', 'featured' => false],
            ['name' => 'Pokeball', 'price' => 45, 'image' => 'pokeball.jpg', 'featured' => true],
            ['name' => 'PinkLady', 'price' => 29.90, 'image' => 'pinklady.jpg', 'featured' => false],
            ['name' => 'Snow', 'price' => 32, 'image' => 'snow.jpg', 'featured' => false],
            ['name' => 'Greyback', 'price' => 28.50, 'image' => 'greyback.jpg', 'featured' => false],
            ['name' => 'BlueCloud', 'price' => 45, 'image' => 'bluecloud.jpg', 'featured' => false],
            ['name' => 'BornInUsa', 'price' => 59.90, 'image' => 'borninusa.jpg', 'featured' => true],
            ['name' => 'GreenSchool', 'price' => 42.20, 'image' => 'greenschool.jpg', 'featured' => false],
        ];

        foreach ($sweatShirts as $data) {
            $sweatShirt = new SweatShirt();
            $sweatShirt->setName($data['name']);
            $sweatShirt->setPrice($data['price']);
            $sweatShirt->setImageFilename($data['image']);
            $sweatShirt->setIsFeatured($data['featured']);
            // Au moins 2 exemplaires par taille demande dans le brief, on met 10
            $sweatShirt->setStockXS(10);
            $sweatShirt->setStockS(10);
            $sweatShirt->setStockM(10);
            $sweatShirt->setStockL(10);
            $sweatShirt->setStockXL(10);
            $manager->persist($sweatShirt);
        }

        // Compte administrateur pour tester le back-office
        $admin = new User();
        $admin->setName('admin');
        $admin->setEmail('admin@stubborn.com');
        $admin->setDeliveryAddress('Piccadilly Circus, London W1J 0DA, Royaume-Uni');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin1234'));
        $manager->persist($admin);

        // Compte client pour tester les achats
        $client = new User();
        $client->setName('client');
        $client->setEmail('client@exemple.com');
        $client->setDeliveryAddress('8 rue du bac, 54100 Nancy');
        $client->setIsVerified(true);
        $client->setPassword($this->passwordHasher->hashPassword($client, 'client1234'));
        $manager->persist($client);

        $manager->flush();
    }
}
