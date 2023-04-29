<?php

namespace App\Controller;

use App\Repository\BagItemRepository;
use App\Repository\BagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Contracts\Translation\TranslatorInterface as TranslationTranslatorInterface;

class StripeController extends AbstractController
{
    #[Route('/stripe', name: 'app_stripe')]
    public function index(BagRepository $bagRepository, BagItemRepository $bagItemRepository,EntityManagerInterface $em,TranslationTranslatorInterface $translator): Response
    {
        
        if (!$this->getUser()) {
            $this->addFlash('warning', $translator->trans('flash.warning.user'));
            return $this->redirectToRoute('app_login');
        }
        $user = $this->getUser();

        $bag = $bagRepository->findOneBy(['useId' => $user, 'status' => '0']);

        $bagItems = $bagItemRepository->findBy(['bagId' => $bag]);

        foreach ($bagItems as $bagItem) {
            $product = $bagItem->getProductId();
            if ($product->getStock() == 0) {
                $em->remove($bagItem);
            }
            else if($bagItem->getQuantity() > $product->getStock()){
                
                $bagItem->setQuantity($product->getStock());
                $em->persist($bagItem);
                $em->flush();
            }
        }
        $em->flush();

        $bagItemFin = $bagItemRepository->findBy(['bagId' => $bag]);

        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
            'bag' => $bag,
            'bagItems' => $bagItemFin,
        ]);
    }

    #[Route('/stripe/payment', name:'stripe_payment')]
    public function payment(BagRepository $bagRepository, BagItemRepository $bagItemRepository,EntityManagerInterface $em,TranslationTranslatorInterface $translator){
        // Récupération de la clé API
        $stripeSecretKey = $this->getParameter('stripe_sk');
        // Initialisation de l'API Stripe
        \Stripe\Stripe::setApiKey($stripeSecretKey);
        $total = 1000;

        try {

            $user = $this->getUser();
            $bag = $bagRepository->findOneBy(['useId' => $user, 'status' => '0']);
            $bagItems = $bagItemRepository->findBy(['bagId' => $bag]);
    
            foreach ($bagItems as $bagItem) {
                $product = $bagItem->getProductId();
                $total = $total + ($product->getPrice() * $bagItem->getQuantity());

                $quantity = $product->getStock();

                $product->setStock($quantity - $bagItem->getQuantity());
                $em->persist($product);
                $em->flush();
            }
            $bag->setStatus(1);
            $bag->setPurchaseDate(new \DateTime());
            $em->persist($bag);
            $em->flush();

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $total,
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
        
            $output = [
                'paymentIntent' => $paymentIntent,
                'clientSecret' => $paymentIntent->client_secret,
            ];
        
            // echo json_encode($output);
            return new JsonResponse($output);
        } catch (\Error $e) {
            // http_response_code(500);
            // echo json_encode(['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
