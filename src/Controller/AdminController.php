<?php

namespace App\Controller;
use App\Entity\User;
use App\Repository\BagItemRepository;
use App\Repository\BagRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Contracts\Translation\TranslatorInterface as TranslationTranslatorInterface;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/user', name: 'app_admin_user')]
    public function userList(UserRepository $userRepository,EntityManagerInterface $em): Response
    {
        $start = new \DateTime('today');
        $end = new \DateTime('tomorrow');
        $end->sub(new \DateInterval('PT1S'));
        
        $users = $userRepository->findUsersRegisteredToday();
        return $this->render('admin/user.html.twig', [
            'users' => $users,
            'start' => $start,
            'end' => $end,
        ]);

    }

    #[Route('/admin/commandes', name: 'app_admin_commandes')]
    public function commandes(BagRepository $bagRepository,BagItemRepository $bagItemRepository, TranslationTranslatorInterface $translator): Response
    {
        
        $bags = $bagRepository->findBy(['status' => '0']);
        
        return $this->render('admin/commandes.html.twig', [
            'bags' => $bags,
        ]);

    }

    #[Route('/admin/command/{id}/', name: 'app_admin_commande')]
    public function commande(int $id,BagRepository $bagRepository,BagItemRepository $bagItemRepository, TranslationTranslatorInterface $translator): Response
    {

        $bagItems = $bagItemRepository->findBy(['bagId' => $id]);
        $bag = $bagRepository->findOneBy(['id' => $id]); 
        
        return $this->render('admin/commande.html.twig', [
            'bag' => $bag,
            'bagItems' => $bagItems,
        ]);

    }



}
