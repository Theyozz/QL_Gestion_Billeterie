<?php

// tests/Entity/EventTest.php

namespace App\Tests\Entity;

use App\Entity\Event;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testEventCreation(): void
    {
        $event = new Event();

        $event->setNom('Tournois League Of Legends')
            ->setDates(['2025-09-10', '2025-09-11'])
            ->setLieu('Lyon')
            ->setCapacite(500)
            ->setPrixParJour([
                '2025-09-10' => 80,
                '2025-09-11' => 90
            ])
            ->setPrixMultipass(150.0);

        // Assertions
        $this->assertEquals('Tournois League Of Legends', $event->getNom());
        $this->assertEquals(['2025-09-10', '2025-09-11'], $event->getDates());
        $this->assertEquals('Lyon', $event->getLieu());
        $this->assertEquals(500, $event->getCapacite());
        $this->assertEquals([
            '2025-09-10' => 80,
            '2025-09-11' => 90
        ], $event->getPrixParJour());
        $this->assertEquals(150.0, $event->getPrixMultipass());
    }
}
