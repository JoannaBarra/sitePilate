<?php





namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Cours;
use App\Entity\User;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/reservation')]
class ReservationController extends AbstractController


{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    #[Route('/', name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {   // Le  token me permet de verifier l'état de l'authentification et le rôle de l'user
        // ça rajoute une sécurité. Il permet de stocker et de fournir le token de sécurité de l'user
        //Le jeton de sécurité contient les identifiants et les rôles de l'user.
        $user = $this->tokenStorage->getToken()->getUser();

        //Je veux retourner uniquement les reservations de l'user connecté
        if (!$user) {
            return new Response('Impossible de récupérer l\'utilisateur connecté.');
        }
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findBy(['user'=> $user]),
        ]);
    }

    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->tokenStorage->getToken()->getUser();

        
        if (!$user) {
            return new Response('Impossible de récupérer l\'utilisateur connecté.');
        }

        $coursId = $request->query->get('coursId');
      
        if ($coursId) {
            $cours = $entityManager->getRepository(Cours::class)->find($coursId);
          
            if ($cours) { 
                $coursDate = $cours->getStart();
                $reservation = new Reservation();
                $reservation->setCour($cours);
                $reservation->setDateResa($coursDate);
                $reservation->setUser($user); // Définir l'utilisateur connecté sur la réservation

                $form = $this->createForm(ReservationType::class, $reservation);
                $form->handleRequest($request);
    
                if ($form->isSubmitted() && $form->isValid()) {
                    //Je décrémente le nb de cours dans ma db.
                    if ($cours->getPlacesDispo() > 0 ) {
                        $cours->setPlacesDispo($cours->getPlacesDispo() - 1);
                        $entityManager->persist($reservation);
                        $entityManager->flush();
                        $this->addFlash(
                            'success',
                            'Réservation enregistré!'
                        );
                        if ($cours->getPlacesDispo() === 1) {
                        $this->addFlash('error', 'Il reste une place!');
                    }

                    return $this->redirectToRoute('app_reservation_show', ['id' => $reservation->getId()]);
                    } else {
                        $this->addFlash('error', 'Plus de disponibilités!');
                        return $this->redirectToRoute('app_reservation_new', ['coursId' => $coursId]);
                    }
                }

                return 
                $this->render('reservation/new.html.twig', [
                    'form' => $form->createView(),
                    'coursId' => $coursId,
                    
                    
                ]);
                
            
    
            
        

            
        return $this->redirectToRoute('app_reservation_index');
    }
}

    
              
         
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
            
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          
            
            $entityManager->flush();
          

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->get('_token'))) {
            // Je réincremente la place disponible dans ma db qui a été supprimé par l'user
            $cours = $reservation->getCour();
            $cours->setPlacesDispo($cours->getPlacesDispo() + 1);
             $entityManager->remove($reservation);
            $entityManager->flush();
            $this->addFlash(
                'success',                              
                'Réservation supprimée!'
            );
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
