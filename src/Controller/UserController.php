<?php

namespace App\Controller;

use App\Entity\BagItem;
use App\Entity\Product;
use App\Entity\User;

use App\Repository\ProductRepository;
use App\Form\ChangePasswordType;
use App\Repository\BagItemRepository;
use App\Repository\BagRepository;
use App\Repository\UserRepository;
use App\Security\AuthAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

use Symfony\Contracts\Translation\TranslatorInterface as TranslationTranslatorInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index( TranslationTranslatorInterface $translator): Response
    {
        if (!$this->getUser()) {

            //$user = $security->getUser();
           // $userId = $user->getId();
           $this->addFlash('warning', $translator->trans('flash.warning.user'));
           return $this->redirectToRoute('app_login');
        }
        $user = $this->getUser();
        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);

    }

    #[Route('/panier', name: 'app_user_panier')]
    public function Panier(BagRepository $bagRepository, BagItemRepository $bagItemRepository,TranslationTranslatorInterface $translator): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', $translator->trans('flash.warning.user'));
            return $this->redirectToRoute('app_login');
        }
        $user = $this->getUser();
        //$bagRepository = $this->getDoctrine()->getRepository(Bag::class);
        $bag = $bagRepository->findOneBy(['useId' => $user, 'status' => '0']);

        $bagItems = $bagItemRepository->findBy(['bagId' => $bag]);
        
        
        return $this->render('user/panier.html.twig', [
            'user' => $user,
            'bag' => $bag,
            'bagItems' => $bagItems,
        ]);

    }
    #[Route('/panier/modifier', name: 'app_user_modifier', methods: ['GET', 'POST'])]    
    public function modifier(Request $request, BagItemRepository $bagItemRepository,EntityManagerInterface $em,ProductRepository $productRepository,
     TranslationTranslatorInterface $translator ): Response
    {

            $action = $request->request->get('action');
        
            //$bagproductId = $product->getId();
            $id = $request->query->get('id');
            $bagItem = $bagItemRepository->findOneBy(['id' => $id]);
            $quantity = $bagItem->getQuantity();
            $productId = $bagItem->getProductId();
            $product = $productRepository->findOneBy(['id' => $productId]);
            $inputValue = $request->get('quantity');
            $changeQuantity = $quantity;

            if($quantity != $inputValue){

                $changeQuantity = $inputValue;
            }else{

                if ($action === 'increment') {  #augmenter +

                    //$bagItem->setQuantity($quantity+1);
                    $changeQuantity++;
                    //$text = 'augmenter +';
    
                } elseif ($action === 'decrement') {  #réduirer -
                    
                    $changeQuantity--;

                }
            }

            if($changeQuantity <= 0){
                $bagItemRepository->remove($bagItem, true);
                return $this->redirectToRoute('app_user_panier', [], Response::HTTP_SEE_OTHER);
            }
            $this->addFlash('success', $translator->trans('flash.success.quantity'));
            if($changeQuantity > $product->getStock()){
                $changeQuantity = $product->getStock();
                $this->addFlash('warning', $translator->trans('flash.warning.max', ['X' => $product->getStock()]));
            }else{
                $this->addFlash('success', $translator->trans('flash.success.quantity'));
            }
                
            $bagItem->setQuantity($changeQuantity);
            $em->persist($bagItem);
            $em->flush();

        return $this->redirectToRoute('app_user_panier', [], Response::HTTP_SEE_OTHER);

    }

    #[Route('/panier/{id}', name: 'app_panier_delete', methods: ['POST'])]
    public function delete(Request $request, BagItem $bagItem, BagItemRepository $bagItemRepository, TranslationTranslatorInterface $translator ): Response
    {
        if($bagItem == null){
            $this->addFlash('danger', $translator->trans('flash.danger.produit'));
        }

        if ($this->isCsrfTokenValid('delete'.$bagItem->getId(), $request->request->get('_token'))) {
            $bagItemRepository->remove($bagItem, true);
            $this->addFlash('success', $translator->trans('flash.success.paniersup'));
        }

        return $this->redirectToRoute('app_user_panier', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/user/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher
    , UserAuthenticatorInterface $userAuthenticator, AuthAuthenticator $authenticator, TranslationTranslatorInterface $translator ): Response
    {
        
        if (!$this->getUser()) {
            $this->addFlash('warning', $translator->trans('flash.warning.user'));
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if($this->getUser() != $user){
            $this->addFlash('warning', $translator->trans('flash.warning.order'));
            return $this->redirectToRoute('app_product_index');
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $form->get('Password')->getData();
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $password
                )
            );

            $userRepository->save($user, true);

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );


            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

}
