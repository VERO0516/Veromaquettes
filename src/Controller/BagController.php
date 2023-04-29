<?php

namespace App\Controller;

use App\Repository\BagItemRepository;
use App\Repository\BagRepository;
use App\Entity\Bag;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface as TranslationTranslatorInterface;

class BagController extends AbstractController
{
    #[Route('/bag', name: 'app_bag')]
    public function index(BagRepository $bagRepository,BagItemRepository $bagItemRepository, TranslationTranslatorInterface $translator): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', $translator->trans('flash.warning.user'));
            return $this->redirectToRoute('app_login');
        }
        $userId = $this->getUser();
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
    public function voircommande(int $id, BagItemRepository $bagItemRepository, BagRepository $bagRepository, TranslationTranslatorInterface $translator): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', $translator->trans('flash.warning.user'));
            return $this->redirectToRoute('app_login');
        }
        $bagItems = $bagItemRepository->findBy(['bagId' => $id]);
        $bag = $bagRepository->findOneBy(['id' => $id]); 
        $userId = $this->getUser();

        if($userId != $bag->getUseId()){
            $this->addFlash('warning', $translator->trans('flash.warning.order'));
            return $this->redirectToRoute('app_bag');
        }
        
        return $this->render('bag/voircommande.html.twig', [
            "bagId" =>$id,
            'bagItems' => $bagItems,
            "bag" => $bag,
        ]);

    }

}
