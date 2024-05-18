<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Repository\CoursRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class ApiController extends AbstractController
{
    
      private EntityManagerInterface $entityManager;
    
        public function __construct(EntityManagerInterface $entityManager)
        {
            $this->entityManager = $entityManager;
        }
    
    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    #[Route('/api/{id}/edit', name: 'app_api_event_edit', methods: ['PUT'])]
    public function majEvent(CoursRepository $coursRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $donnees = json_decode($request->getContent());
        if (
            isset($donnees->id) && !empty($donnees->id) &&
            isset($donnees->start) && !empty($donnees->start) &&
            isset($donnees->end) && !empty($donnees->end)
        ) {
            $cours = $coursRepository->findOneBy(["id" => $donnees->id]);
            $cours->setStart(new DateTime($donnees->start));
            $cours->setEnd(new DateTime($donnees->end));
            $entityManager->persist($cours);
            $entityManager->flush();
            
            return new Response("La victoire!");
        } else {
            return new Response('Données incomplètes!', 404);
        }
      
    }
}
