<?php

namespace App\Tests\Controller;

use App\Controller\OrderController;
use App\Entity\Event;
use App\Entity\Order;
use App\Repository\EventRepository;
use App\Repository\OrderRepository;
use App\Service\TicketService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderControllerTest extends WebTestCase
{
    public function testNewOrderRoute(): void
    {
        $client = static::createClient();

        // Mock EventRepository
        $mockRepo = $this->createMock(EventRepository::class);
        $event = (new Event())
            ->setNom('Mock Event')
            ->setLieu('Mock City')
            ->setCapacite(100)
            ->setDates(['2025-09-10'])
            ->setPrixParJour(['2025-09-10' => 50])
            ->setPrixMultipass(80);
        $mockRepo->method('findAll')->willReturn([$event]);

        $client->getContainer()->set(EventRepository::class, $mockRepo);

        $crawler = $client->request('GET', '/order/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testCreateOrderWithValidData(): void
    {
        $client = static::createClient();

        // Mock Event
        $event = (new Event())
            ->setNom('Mock Event')
            ->setLieu('Mock City')
            ->setCapacite(100)
            ->setDates(['2025-09-10'])
            ->setPrixParJour(['2025-09-10' => 50])
            ->setPrixMultipass(80);

        // Mock EventRepository
        $eventRepo = $this->createMock(EventRepository::class);
        $eventRepo->method('find')->willReturn($event);
        $client->getContainer()->set(EventRepository::class, $eventRepo);

        // Mock Order
        $order = new Order();

        // Mock TicketService
        $ticketService = $this->createMock(TicketService::class);
        $ticketService->method('createOrderWithTickets')->willReturn($order);
        $client->getContainer()->set(TicketService::class, $ticketService);

        // Mock OrderRepository
        $orderRepo = $this->createMock(OrderRepository::class);
        $orderRepo->expects($this->once())->method('save');
        $client->getContainer()->set(OrderRepository::class, $orderRepo);

        $client->request('POST', '/order/create', [
            'event_id' => 1, // ID fictif
            'email' => 'john@example.com',
            'date' => '2025-09-10',
            'ticket_names' => ['Doe'],
            'ticket_firstnames' => ['John'],
            'ticket_types' => ['jour'],
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testCreateOrderWithMissingData(): void
    {
        $client = static::createClient();

        // Mocks avec `find` qui retourne null
        $eventRepo = $this->createMock(EventRepository::class);
        $eventRepo->method('find')->willReturn(null);
        $client->getContainer()->set(EventRepository::class, $eventRepo);

        $client->request('POST', '/order/create', []);

        $this->assertResponseRedirects('/order/new');
        $client->followRedirect();
    }
}
