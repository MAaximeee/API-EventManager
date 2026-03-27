<?php

namespace App\DataFixtures;

use App\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $EventsData = [
            [
                'title' => 'Configurer l\'environnement',
                'description' => 'Installer WAMP et Composer',
                'status' => Event::STATUS_COMPLETED,
                'type' => 'task',
            ],
            [
                'title' => 'Creer l\'API REST',
                'description' => 'Implementer les endpoints CRUD',
                'status' => Event::STATUS_IN_PROGRESS,
                'type' => 'event',
            ],
            [
                'title' => 'Ajouter l\'authentification',
                'description' => 'Implementer JWT',
                'status' => Event::STATUS_PENDING,
                'type' => 'meeting',
            ],
        ];

        foreach ($EventsData as $data) {
            $event = new Event();
            $event->setTitle($data['title']);
            $event->setDescription($data['description']);
            $event->setStatus($data['status']);
            $event->setType($data['type']);

            $manager->persist($event);
        }

        $manager->flush();
    }
}