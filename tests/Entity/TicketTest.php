<?php

namespace App\Tests\Entity;

use App\Entity\Ticket;
use PHPUnit\Framework\TestCase;

class TicketTest extends TestCase
{
    public function testTicketCreation(): void
    {
        $ticket = new Ticket();
        $ticket->setNom('Doe')
            ->setPrenom('John')
            ->setType('journée')
            ->setPrix(25.0);

        $this->assertSame('Doe', $ticket->getNom());
        $this->assertSame('John', $ticket->getPrenom());
        $this->assertSame('journée', $ticket->getType());
        $this->assertSame(25.0, $ticket->getPrix());
    }
}
