<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Order;
use App\Entity\Ticket;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
    
        // Création d'un événement factice
        $event = new Event();
        $event->setNom($faker->company)
              ->setLieu($faker->city)
              ->setCapacite(100)
              ->setPrixMultipass($faker->randomFloat(2, 20, 200))
              ->setDates([$faker->dateTimeThisYear(), $faker->dateTimeThisYear()])
              ->setPrixParJour([
                  $faker->dateTimeThisYear()->format('Y-m-d') => $faker->randomFloat(2, 30, 100),
              ]);
        $manager->persist($event);
    
        // Création de 200 commandes, chacune avec 100 tickets
        for ($i = 0; $i < 200; $i++) {  // Pour créer 200 commandes
            $order = new Order();
            $order->setEvent($event); // Associer l'événement à la commande
            $order->setDateCommande($faker->dateTimeThisYear());
            $order->setTotal($faker->randomFloat(2, 50, 500)); // Total aléatoire pour la commande
            $manager->persist($order);
    
            // Génération des 100 tickets associés à cette commande
            for ($j = 0; $j < 100; $j++) { // Pour chaque commande, générer 100 tickets
                $ticket = new Ticket();
                $ticket->setType($faker->randomElement(['VIP', 'Standard', 'Premium']));
                $ticket->setNom($faker->lastName());
                $ticket->setPrenom($faker->firstName());
                $ticket->setEmail($faker->email());
                $ticket->setDate($faker->optional()->dateTimeBetween('-1 year', '+1 year'));
                $ticket->setPrix($faker->randomFloat(2, 5, 150));
                $ticket->setOrder($order);
    
                $manager->persist($ticket);
            }
    
            // Pour éviter de dépasser la mémoire, on fait un flush tous les 100 tickets
            if ($i % 10 === 0) {  // J'ai changé ici pour 10 commandes pour ne pas surcharger la mémoire
                $manager->flush();
                $manager->clear(); // Libérer la mémoire après chaque flush
            }
        }
    
        $manager->flush(); // Final flush pour persister toutes les données restantes
    }
    
}
