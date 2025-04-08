<?php
namespace App\Controller;

use App\Entity\Order;
use App\Entity\Ticket;
use App\Entity\Participant;
use App\Service\TicketService;
use App\Repository\EventRepository;
use App\Repository\OrderRepository;
use App\Repository\ParticipantRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/api/order', name: 'api_create_order', methods: ['POST'])]
    public function createOrder(
        Request $request,
        TicketService $ticketService,
        EventRepository $eventRepository,
        ParticipantRepository $participantRepository,
        OrderRepository $orderRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $participant = $participantRepository->find($data['participant_id']);
        $event = $eventRepository->find($data['event_id']);
        $date = $data['date'] ?? null;
        $ticketsData = $data['tickets'] ?? [];

        if (!$participant || !$event || empty($ticketsData)) {
            return $this->json(['error' => 'Invalid data'], 400);
        }

        // Création de l'ordre avec les tickets
        $order = $ticketService->createOrderWithTickets($participant, $event, $date, $ticketsData);

        // Sauvegarde de l'ordre en BDD
        $orderRepository->save($order);

        return $this->json([
            'message' => 'Order successfully created',
            'order_id' => $order->getId(),
            'total' => $order->getTotal(),
            'ticket_count' => count($order->getTickets())
        ], 201);
    }

    #[Route('/api/test-order', name: 'api_test_order', methods: ['GET'])]
    public function testOrder(
        OrderRepository $orderRepository,
        ParticipantRepository $participantRepository,
        EventRepository $eventRepository
    ): JsonResponse {
        // On récupère un Event et un Participant existants (utilise les IDs que tu as dans ta BDD)
        $event = $eventRepository->find(1);
        $participant = $participantRepository->find(1);

        if (!$event || !$participant) {
            return $this->json(['error' => 'Event or Participant not found.'], 404);
        }

        // Création d'un Order avec un ticket
        $order = new Order();
        $order->setParticipant($participant);
        $order->setEvent($event);
        $order->setDateCommande(new DateTime());

        // Création d'un Ticket pour ce jour
        $ticket = new Ticket();
        $ticket->setNom('Durand');
        $ticket->setPrenom('Alice');
        $ticket->setType('jour');
        $ticket->setDate(new DateTime($event->getDates()[0]));
        $ticket->setPrix($event->getPrixParJour()[$event->getDates()[0]]);
        $ticket->setOrder($order);

        // Ajout du ticket à l'ordre
        $order->addTicket($ticket);
        $order->setTotal($ticket->getPrix());

        // Sauvegarde en BDD
        $orderRepository->save($order);

        return $this->json([
            'message' => 'Order created successfully ✅',
            'order_id' => $order->getId(),
            'total' => $order->getTotal()
        ]);
    }
}
