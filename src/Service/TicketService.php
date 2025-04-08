<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Ticket;
use App\Entity\Event;
use App\Entity\Participant;
use DateTime;

class TicketService
{
    /**
     * Crée une commande avec tickets et participant.
     *
     * @param Participant $participant L'utilisateur passant la commande.
     * @param Event $event L'événement lié.
     * @param string|null $date La date sélectionnée si ticket simple.
     * @param array $ticketsData Liste des infos tickets (nom, prénom, type).
     * @return Order
     */
    public function createOrderWithTickets(Participant $participant, Event $event, ?string $date, array $ticketsData): Order
    {
        $order = new Order();
        $order->setParticipant($participant);
        $order->setEvent($event);
        $order->setDateCommande(new DateTime());

        $total = 0;

        foreach ($ticketsData as $ticketInfo) {
            $ticket = new Ticket();
            $ticket->setNom($ticketInfo['nom']);
            $ticket->setPrenom($ticketInfo['prenom']);
            $ticket->setType($ticketInfo['type']);

            if ($ticketInfo['type'] === 'multipass') {
                $ticket->setPrix($event->getPrixMultipass());
                $ticket->setDate(null); // Pas de date spécifique
                $total += $event->getPrixMultipass();
            } else {
                if ($date) {
                    $ticketDate = new DateTime($date);
                    $ticket->setDate($ticketDate);
                    $prix = $event->getPrixParJour()[$date] ?? 0;
                    $ticket->setPrix($prix);
                    $total += $prix;
                }
            }

            $order->addTicket($ticket);
        }

        $order->setTotal($total);

        return $order;
    }
}
