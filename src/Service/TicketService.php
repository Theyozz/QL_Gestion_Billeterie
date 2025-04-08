<?php
namespace App\Service;

use App\Entity\Order;
use App\Entity\Ticket;
use App\Entity\Event;
use DateTime;

class TicketService
{
    /**
     * Crée une commande avec tickets et participant.
     *
     * @param string $email L'email de l'utilisateur passant la commande.
     * @param Event $event L'événement lié.
     * @param string|null $date La date sélectionnée si ticket simple.
     * @param array $ticketsData Liste des infos tickets (nom, prénom, type).
     * @return Order
     */
    public function createOrderWithTickets(string $email, Event $event, ?string $date, array $ticketsData): Order
    {
        // Crée la commande
        $order = new Order();
        $order->setEvent($event);
        $order->setDateCommande(new DateTime());

        // Calcul du total
        $total = 0;

        // Boucle pour créer les tickets
        foreach ($ticketsData as $ticketInfo) {
            $ticket = new Ticket();
            $ticket->setNom($ticketInfo['nom']);
            $ticket->setPrenom($ticketInfo['prenom']);
            $ticket->setType($ticketInfo['type']);
            $ticket->setEmail($email);  // Associe l'email du participant au ticket

            // Traitement en fonction du type de ticket
            if ($ticketInfo['type'] === 'multipass') {
                $ticket->setPrix($event->getPrixMultipass());
                $ticket->setDate(null);  // Pas de date spécifique pour un multipass
                $total += $event->getPrixMultipass();
            } else {
                // Ticket avec date
                if ($date) {
                    $ticketDate = new DateTime($date);
                    $ticket->setDate($ticketDate);
                    $prix = $event->getPrixParJour()[$date] ?? 0;
                    $ticket->setPrix($prix);
                    $total += $prix;
                }
            }

            // Ajoute le ticket à la commande
            $order->addTicket($ticket);
        }

        // Met à jour le prix total de la commande
        $order->setTotal($total);

        return $order;
    }
}
