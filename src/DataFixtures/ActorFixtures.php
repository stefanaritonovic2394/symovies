<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;

class ActorFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create actors with names
        $actorsData = [
            'Christian Bale',
            'Aaron Eckhart',
            'Heath Ledger',
            'Chris Evans',
            'Robert Downey Jr.',
            'Scarlett Johansson'
        ];

        foreach ($actorsData as $index => $name) {
            $currentDate = new DateTime();
            $actor = new Actor();
            $actor->setName($name);
            $actor->setCreatedAt($currentDate);
            $manager->persist($actor);

            $this->addReference('actor_' . ($index + 1), $actor);
        }

        $manager->flush();
    }
}
