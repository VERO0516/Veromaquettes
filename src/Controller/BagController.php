<?php

namespace App\Controller;

use App\Repository\BagItemRepository;
use App\Repository\BagRepository;
use App\Entity\Bag;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BagController extends AbstractController
{
    #[Route('/bag', name: 'app_bag')]
    public function index(BagRepository $bagRepository,BagItemRepository $bagItemRepository): Response
    {
        if ($this->getUser()) {

            // $user = $security->getUser();
            // $userId = $user->getId();
            $userId = $this->getUser();
        }
        $bags = $bagRepository->findBy(['useId' => $userId, 'status' => '1']);

        $totalPrices = [];
        foreach ($bags as $bag) {
            $items = $bagItemRepository->findBy(['bagId' => $bag->getId()]);
            $totalPrice = 0;
            foreach ($items as $item) {
                $totalPrice += $item->getQuantity() * $item->getProductId()->getPrice();
            }
            $totalPrices[] = $totalPrice;
        }
        
        
        return $this->render('bag/index.html.twig', [
            'user' => $userId,
            'bags' => $bags,
            'totalPrices' => $totalPrices,
        ]);
    }

    #[Route('/voircommande/{id}/', name: 'app_bag_voircommande' )]
    public function voircommande(int $id, BagItemRepository $bagItemRepository, BagRepository $bagRepository): Response
    {
        $bagItems = $bagItemRepository->findBy(['bagId' => $id]);

        $bag = $bagRepository->findOneBy(['id' => $id]);  
        
        return $this->render('bag/voircommande.html.twig', [
            "bagId" =>$id,
            'bagItems' => $bagItems,
            "bag" => $bag,
        ]);

    }

}
