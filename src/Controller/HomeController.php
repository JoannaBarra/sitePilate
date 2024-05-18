<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #[Route('/creeruser', name: 'app_creer_user')]
    public function creerUser(UserPasswordHasherInterface $chiffreur,
    EntityManagerInterface $em
    ): Response
    {
        $user = new User();
        $user->setUsername('user');
        $motDePasse = $chiffreur->hashPassword($user, 'user');
        $user->setPassword($motDePasse);
        $user->setRoles(['ROLE_USER']);

        $em->persist($user);
        $em->flush();
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    
}
}