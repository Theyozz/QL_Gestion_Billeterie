<?php
namespace App\Controller;

use App\Entity\Order;
use App\Entity\Ticket;
use App\Service\TicketService;
use App\Repository\EventRepository;
use App\Repository\OrderRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/order/new', name: 'order_form', methods: ['GET'])]
    public function newOrder(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        $eventDatesWithPrices = [];
        
        foreach ($events as $event) {
            // Utiliser la méthode getDatesWithPrices pour obtenir les dates et prix
            $eventDatesWithPrices[$event->getId()] = $event->getDatesWithPrices();
        }
    
        return $this->render('order/form.html.twig', [
            'events' => $events,
            'event_dates' => $eventDatesWithPrices  // Passer les dates avec les prix au template
        ]);
    }
    #[Route('/order/create', name: 'order_create', methods: ['POST'])]
    public function createOrderFromForm(
        Request $request,
        TicketService $ticketService,
        EventRepository $eventRepository,
        OrderRepository $orderRepository
    ): Response 
    {
        $eventId = $request->request->get('event_id');
        $email = $request->request->get('email');
        $date = $request->request->get('date'); // peut être null pour multipass

        // Exemple de structure attendue : plusieurs tickets
        $ticketsData = [];
        $names = $request->request->all()['ticket_names'] ?? [];
        if (!is_array($names)) {
            $names = []; // fallback just in case
        }
        $firstnames = $request->request->all()['ticket_firstnames'] ?? [];
        $types = $request->request->all()['ticket_types'] ?? [];

        for ($i = 0; $i < count($names); $i++) {
            $ticketsData[] = [
                'nom' => $names[$i],
                'prenom' => $firstnames[$i],
                'type' => $types[$i],
            ];
        }

        $event = $eventRepository->find($eventId);

        if (!$event || !$email || empty($ticketsData)) {
            $this->addFlash('error', 'Informations invalides.');
            return $this->redirectToRoute('order_form');
        }

        $order = $ticketService->createOrderWithTickets($email, $event, $date, $ticketsData);
        $orderRepository->save($order);

        return $this->render('order/confirmation.html.twig', [
            'order' => $order
        ]);
    }

}
