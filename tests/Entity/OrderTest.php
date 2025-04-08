<?php
namespace App\Tests\Entity;

use App\Entity\Order;
use App\Entity\Ticket;
use App\Entity\Event;
use App\Service\TicketService;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testCreateOrderWithSingleTicket(): void
    {
        $event = new Event();
        $event->setNom('Festival Unique');
        $event->setLieu('Marseille');
        $event->setCapacite(500);
        $event->setDates(['2025-09-15']);
        $event->setPrixParJour(['2025-09-15' => 70]);
        $event->setPrixMultipass(100);

        $email = 'john.doe@example.com';
        $ticketsData = [
            ['nom' => 'Doe', 'prenom' => 'John', 'type' => 'jour']
        ];

        $service = new TicketService();
        $order = $service->createOrderWithTickets($email, $event, '2025-09-15', $ticketsData);

        // Vérifier l'email à partir des tickets de la commande
        $ticket = $order->getTickets()->first();  // Récupère le premier ticket
        $this->assertEquals('john.doe@example.com', $ticket->getEmail());
        $this->assertCount(1, $order->getTickets());
        $this->assertEquals(70, $order->getTotal());

        $this->assertEquals('Doe', $ticket->getNom());
        $this->assertEquals('John', $ticket->getPrenom());
        $this->assertEquals('2025-09-15', $ticket->getDate()->format('Y-m-d'));
        $this->assertEquals(70, $ticket->getPrix());
    }

    public function testOrderWithMultipleTicketsSameDay(): void
    {
        $event = new Event();
        $event->setNom('Festival Test')
            ->setLieu('Paris')
            ->setCapacite(500)
            ->setDates(['2025-07-12', '2025-07-13'])
            ->setPrixParJour(['2025-07-12' => 40])
            ->setPrixMultipass(70);

        $email = 'martin.bob@email.com';

        $ticketsData = [
            ['nom' => 'Dupont', 'prenom' => 'Alice', 'type' => 'jour'],
            ['nom' => 'Martin', 'prenom' => 'Bob', 'type' => 'jour']
        ];

        $service = new TicketService();
        $order = $service->createOrderWithTickets($email, $event, '2025-07-12', $ticketsData);

        // Vérifier l'email des tickets associés à la commande
        foreach ($order->getTickets() as $ticket) {
            $this->assertEquals('martin.bob@email.com', $ticket->getEmail());
        }

        $this->assertCount(2, $order->getTickets());
        $this->assertEquals(80.0, $order->getTotal());

        foreach ($order->getTickets() as $ticket) {
            $this->assertEquals(new \DateTime('2025-07-12'), $ticket->getDate());
        }
    }

    public function testTicketPriceBasedOnEventDay(): void
    {
        $event = new Event();
        $event->setNom('Lyon ESport');
        $event->setLieu('Lyon');
        $event->setCapacite(1500);
        $event->setDates(['2025-06-14', '2025-06-15', '2025-06-16']);
        $event->setPrixParJour([
            '2025-06-14' => 10,
            '2025-06-15' => 12,
            '2025-06-16' => 15
        ]);
        $event->setPrixMultipass(30);

        $email = 'sarah.lemoine@example.com';
        $ticketsData = [
            ['nom' => 'Lemoine', 'prenom' => 'Sarah', 'type' => 'single']
        ];

        $service = new TicketService();
        $order = $service->createOrderWithTickets($email, $event, '2025-06-14', $ticketsData);

        $this->assertCount(1, $order->getTickets());
        $this->assertEquals(10, $order->getTotal());
    }

    public function testCreateMultipassOrderWithMultiplePeople(): void
    {
        $event = new Event();
        $event->setNom('Lyon ESport');
        $event->setLieu('Lyon');
        $event->setCapacite(1500);
        $event->setDates(['2025-06-14', '2025-06-15', '2025-06-16']);
        $event->setPrixParJour([
            '2025-06-14' => 10,
            '2025-06-15' => 12,
            '2025-06-16' => 15
        ]);
        $event->setPrixMultipass(30);

        $email = 'sarah.lemoine@example.com';

        $ticketsData = [
            ['nom' => 'Lemoine', 'prenom' => 'Sarah', 'type' => 'multipass'],
            ['nom' => 'Durand', 'prenom' => 'Yanis', 'type' => 'multipass'],
        ];

        $service = new TicketService();
        $order = $service->createOrderWithTickets($email, $event, null, $ticketsData);

        $this->assertCount(2, $order->getTickets());
        $this->assertEquals(60, $order->getTotal());

        foreach ($order->getTickets() as $ticket) {
            $this->assertEquals(30, $ticket->getPrix());
            $this->assertEquals('multipass', $ticket->getType());
            $this->assertNull($ticket->getDate());
        }

        echo "\n🧾 Multipass commandés :";
        foreach ($order->getTickets() as $ticket) {
            echo "\n - {$ticket->getPrenom()} {$ticket->getNom()} : {$ticket->getPrix()}€ (Multipass)";
        }
    }
}
