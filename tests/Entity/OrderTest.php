<?php
namespace App\Tests\Entity;

use App\Entity\Order;
use App\Entity\Ticket;
use App\Entity\Event;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testOrderWithMultipleTicketsSameDay(): void
    {
        $event = new Event();
        $event->setNom('Festival Test')
            ->setDates(['2025-07-12', '2025-07-13'])
            ->setLieu('Paris')
            ->setCapacite(500)
            ->setPrixParJour(['2025-07-12' => 40])
            ->setPrixMultipass(70);

        $order = new Order();
        $order->setEmail('martin.bob@email.com')
            ->setDateCommande(new \DateTime())
            ->setEvent($event)
            ->setTotal(0);

        $ticket1 = new Ticket();
        $ticket1->setNom('Dupont')
            ->setPrenom('Alice')
            ->setType('journee')
            ->setPrix(40.0)
            ->setDate(new \DateTime('2025-07-12'))
            ->setOrder($order);

        $ticket2 = new Ticket();
        $ticket2->setNom('Martin')
            ->setPrenom('Bob')
            ->setType('journee')
            ->setPrix(40.0)
            ->setDate(new \DateTime('2025-07-12'))
            ->setOrder($order);

        $order->addTicket($ticket1);
        $order->addTicket($ticket2);

        $order->setTotal($ticket1->getPrix() + $ticket2->getPrix());

        // 👇 DEBUG OUTPUT
        echo "\nCommande créée pour : " . $order->getEmail();
        echo "\nNombre de billets : " . count($order->getTickets());
        echo "\nTotal : " . $order->getTotal() . "€";

        foreach ($order->getTickets() as $i => $ticket) {
            echo "\nTicket #" . ($i + 1);
            echo "\n  - Nom : " . $ticket->getNom();
            echo "\n  - Prénom : " . $ticket->getPrenom();
            echo "\n  - Date : " . $ticket->getDate()->format('Y-m-d');
            echo "\n  - Prix : " . $ticket->getPrix() . "€";
        }

        $this->assertCount(2, $order->getTickets());
        $this->assertEquals(80.0, $order->getTotal());
        $this->assertEquals('client@example.com', $order->getEmail());

        foreach ($order->getTickets() as $ticket) {
            $this->assertEquals(new \DateTime('2025-07-12'), $ticket->getDate());
            $this->assertSame($order, $ticket->getOrder());
        }
    }
}
