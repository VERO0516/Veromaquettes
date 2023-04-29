<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\BagItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BagItemController extends AbstractController
{
    #[Route('/bag/item', name: 'app_bag_item')]
    public function index(Request $request, EntityManagerInterface $em, Request $r): Response
    {
        
        $quantity = (int)$request->request->get('quantity');
        $Userid = $request->get('product_id');


        $bagItem = new BagItem();
        $form = $this->createForm(MarkType::class, $bagItem);
        $form->handleRequest($r);

        if($form->isSubmitted() && $form->isValid()){
            $this->addFlash("warning", "成功 ");
            $em->persist($bagItem);
            $em->flush();
        }
        else{
            $this->addFlash("warning", "失败 ");
        }

        return $this->render('bag_item/index.html.twig', [
            'controller_name' => 'BagItemController',
            'quantity' => $quantity,
            'Userid' => $Userid,
        ]);
    }
}
