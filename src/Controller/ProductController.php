<?php

namespace App\Controller;

use App\Entity\Bag;
use App\Entity\BagItem;
use App\Form\BagItemType;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\BagItemRepository;
use App\Repository\BagRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

// #[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/product/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $product = new Product();

            $form = $this->createForm(ProductType::class,$product);
    
            $form->handleRequest($request);

            $imageFile = $form->get('image')->getData();

            if ($imageFile) {

                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash("warning", "Impossible d'ajouter l'image ");
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $product->setImage($newFilename);
            }
            $em->persist($product);
            $em->flush();
            $this->addFlash('sucess', 'product.added');
            

        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }




    #[Route('/product/{id}', name: 'app_product_show', methods: ['GET', 'POST'])]
    public function show(Product $product,EntityManagerInterface $em, Request $r,BagItemRepository $bagItemRepository, 
    BagRepository $bagRepository): Response
    {

 
        $bagItem = new BagItem();
        //$form = $this->createForm(BagItemType::class, $bagItem);
        //$form->handleRequest($r);

        $maxQuantity = $product->getStock();
        $form = $this->createForm(BagItemType::class, $bagItem, ['max_quantity' => $maxQuantity]);
        $form->handleRequest($r);

        if($form->isSubmitted() && $form->isValid() && $form->get('quantity')->getData() > 0){

            $user = $this->getUser();

            $bag = $bagRepository->findOneBy(['useId' => $user, 'status' => '0']);

            if(!$bag){
                $bag = new Bag();
                $bag->setStatus(0);
                $bag->setUseId($user);
                $em->persist($bag);
                $em->flush();
            }
            
            $bagItem->setProductId($product);

            $bagproductId = $product->getId();
            
            $bagId = $bagRepository->findOneBy(['useId' => $user, 'status' => '0']);
            $bagItem->setbagId($bagId);

            $existingBagItem = $bagItemRepository->findOneBy([
                'ProductId' => $bagproductId,
                'bagId' => $bagId,
            ]);
            
            if(!$existingBagItem){
                $productQuantity = $form->get('quantity')->getData();
                if($productQuantity >  $maxQuantity){

                    $bagItem->setQuantity($maxQuantity);
                }
                else{
                    $bagItem->setQuantity($productQuantity);
                } 

                $em->persist($bagItem);
            }
            else{
                $productQuantity = $form->get('quantity')->getData();
                $existingQuantity = $existingBagItem->getQuantity();
                $newQuantity = $existingQuantity + $productQuantity;

                if($newQuantity >  $maxQuantity){
                    $existingBagItem->setQuantity($maxQuantity);
                }
                else{
                    $existingBagItem->setQuantity($newQuantity);
                }  

            }
            $em->flush();

        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'form' => $form->createView(), 
        ]);
    }

    #[Route('/product/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    
}
