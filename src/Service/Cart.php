<?php

namespace App\Service;

use App\Repository\SweatShirtRepository;
use Symfony\Component\HttpFoundation\RequestStack;

// Le panier est stocke en session, sous la forme :
// ['1-M' => ['sweatShirtId' => 1, 'size' => 'M', 'quantity' => 2], ...]
class Cart
{
    private const SESSION_KEY = 'panier';

    public function __construct(
        private RequestStack $requestStack,
        private SweatShirtRepository $sweatShirtRepository,
    ) {
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    public function add(int $sweatShirtId, string $size): void
    {
        $panier = $this->getSession()->get(self::SESSION_KEY, []);
        $cle = $sweatShirtId . '-' . $size;

        if (isset($panier[$cle])) {
            $panier[$cle]['quantity']++;
        } else {
            $panier[$cle] = [
                'sweatShirtId' => $sweatShirtId,
                'size' => $size,
                'quantity' => 1,
            ];
        }

        $this->getSession()->set(self::SESSION_KEY, $panier);
    }

    public function remove(string $cle): void
    {
        $panier = $this->getSession()->get(self::SESSION_KEY, []);
        unset($panier[$cle]);
        $this->getSession()->set(self::SESSION_KEY, $panier);
    }

    public function clear(): void
    {
        $this->getSession()->remove(self::SESSION_KEY);
    }

    // Renvoie les lignes du panier avec le sweat-shirt complet (nom, prix, image)
    public function getItems(): array
    {
        $panier = $this->getSession()->get(self::SESSION_KEY, []);
        $lignes = [];

        foreach ($panier as $cle => $ligne) {
            $sweatShirt = $this->sweatShirtRepository->find($ligne['sweatShirtId']);

            if (!$sweatShirt) {
                continue;
            }

            $lignes[] = [
                'cle' => $cle,
                'sweatShirt' => $sweatShirt,
                'size' => $ligne['size'],
                'quantity' => $ligne['quantity'],
                'sousTotal' => $sweatShirt->getPrice() * $ligne['quantity'],
            ];
        }

        return $lignes;
    }

    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getItems() as $ligne) {
            $total += $ligne['sousTotal'];
        }

        return $total;
    }
}
