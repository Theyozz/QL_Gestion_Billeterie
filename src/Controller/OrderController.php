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
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/api/order', name: 'api_create_order', methods: ['POST'])]
    public function createOrder(
        Request $request,
        TicketService $ticketService,
        EventRepository $eventRepository,
        OrderRepository $orderRepository
    ): JsonResponse {
        // Récupération des données envoyées en POST
        $data = json_decode($request->getContent(), true);

        // Vérification des données nécessaires
        $event = $eventRepository->find($data['event_id']);
        $email = $data['email'] ?? null;
        $date = $data['date'] ?? null;
        $ticketsData = $data['tickets'] ?? [];

        if (!$email || !$event || empty($ticketsData)) {
            return $this->json(['error' => 'Invalid data'], 400);
        }

        // Création de la commande avec les tickets
        $order = $ticketService->createOrderWithTickets($email, $event, $date, $ticketsData);

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
        EventRepository $eventRepository
    ): JsonResponse {
        // Récupération d'un Event existant
        $event = $eventRepository->find(1);
    
        if (!$event) {
            return $this->json(['error' => 'Event not found.'], 404);
        }
    
        // Création d'un Order
        $order = new Order();
        $order->setEvent($event);
        $order->setDateCommande(new DateTime());
    
        // Exemples de tickets à ajouter
        $ticketInfos = [
            [
                'nom' => 'Durand',
                'prenom' => 'Alice',
                'type' => 'jour',
                'email' => 'alice@example.com',
                'date' => $event->getDates()[0]
            ],
            [
                'nom' => 'Martin',
                'prenom' => 'Bob',
                'type' => 'multipass',
                'email' => 'bob@example.com',
                'date' => null
            ]
        ];
    
        $total = 0;
    
        foreach ($ticketInfos as $info) {
            $ticket = new Ticket();
            $ticket->setNom($info['nom']);
            $ticket->setPrenom($info['prenom']);
            $ticket->setType($info['type']);
            $ticket->setEmail($info['email']);
            $ticket->setOrder($order);
    
            if ($info['type'] === 'multipass') {
                $ticket->setPrix($event->getPrixMultipass());
                $ticket->setDate(null);
                $total += $event->getPrixMultipass();
            } else {
                $date = new DateTime($info['date']);
                $ticket->setDate($date);
                $prix = $event->getPrixParJour()[$info['date']] ?? 0;
                $ticket->setPrix($prix);
                $total += $prix;
            }
    
            $order->addTicket($ticket);
        }
    
        $order->setTotal($total);
    
        // Sauvegarde
        $orderRepository->save($order);
    
        return $this->json([
            'message' => 'Order with multiple tickets created ✅',
            'order_id' => $order->getId(),
            'total' => $order->getTotal(),
            'ticket_count' => count($order->getTickets())
        ]);
    }    
}
