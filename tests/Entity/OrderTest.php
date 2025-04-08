<?php
namespace App\Tests\Entity;

use App\Entity\Order;
use App\Entity\Ticket;
use App\Entity\Event;
use App\Entity\Participant;
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

        $participant = new Participant();
        $participant->setNom('Doe')->setPrenom('John')->setEmail('john.doe@example.com')->setEvent($event);

        $ticketsData = [
            ['nom' => 'Doe', 'prenom' => 'John', 'type' => 'jour']
        ];

        $service = new TicketService();
        $order = $service->createOrderWithTickets($participant, $event, '2025-09-15', $ticketsData);

        $this->assertEquals('john.doe@example.com', $order->getParticipant()->getEmail());
        $this->assertCount(1, $order->getTickets());
        $this->assertEquals(70, $order->getTotal());

        $ticket = $order->getTickets()->first();
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

        $participant = new Participant();
        $participant->setNom('Martin')->setPrenom('Bob')->setEmail('martin.bob@email.com')->setEvent($event);

        $order = new Order();
        $order->setDateCommande(new \DateTime())
            ->setEvent($event)
            ->setParticipant($participant)
            ->setTotal(0);

        $ticket1 = new Ticket();
        $ticket1->setNom('Dupont')->setPrenom('Alice')->setType('journee')->setPrix(40.0)->setDate(new \DateTime('2025-07-12'))->setOrder($order);

        $ticket2 = new Ticket();
        $ticket2->setNom('Martin')->setPrenom('Bob')->setType('journee')->setPrix(40.0)->setDate(new \DateTime('2025-07-12'))->setOrder($order);

        $order->addTicket($ticket1);
        $order->addTicket($ticket2);
        $order->setTotal($ticket1->getPrix() + $ticket2->getPrix());

        $this->assertCount(2, $order->getTickets());
        $this->assertEquals(80.0, $order->getTotal());
        $this->assertEquals('martin.bob@email.com', $order->getParticipant()->getEmail());

        foreach ($order->getTickets() as $ticket) {
            $this->assertEquals(new \DateTime('2025-07-12'), $ticket->getDate());
            $this->assertSame($order, $ticket->getOrder());
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

        $participant = new Participant();
        $participant->setNom('Lemoine')->setPrenom('Sarah')->setEmail('sarah.lemoine@example.com')->setEvent($event);

        $ticketsData = [
            ['nom' => 'Lemoine', 'prenom' => 'Sarah', 'type' => 'single']
        ];

        $service = new TicketService();
        $order = $service->createOrderWithTickets($participant, $event, '2025-06-14', $ticketsData);

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

        $participant = new Participant();
        $participant->setNom('Lemoine')->setPrenom('Sarah')->setEmail('sarah.lemoine@example.com')->setEvent($event);

        $ticketsData = [
            ['nom' => 'Lemoine', 'prenom' => 'Sarah', 'type' => 'multipass'],
            ['nom' => 'Durand', 'prenom' => 'Yanis', 'type' => 'multipass'],
        ];

        $service = new TicketService();
        $order = $service->createOrderWithTickets($participant, $event, null, $ticketsData);

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
