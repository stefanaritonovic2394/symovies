<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MovieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $currentDate= new DateTime();

        $movie = new Movie();
        $movie->setTitle('Avengers: Endgame');
        $movie->setDescription('After the devastating events of Avengers: Infinity War (2018), the universe is in ruins. With the help of remaining allies, the Avengers assemble once more in order to reverse Thanos actions and restore balance to the universe.');
        $movie->setReleaseYear(2019);
        $movie->setCreatedAt($currentDate);
        $movie->setImagePath('https://cdn.pixabay.com/photo/2022/06/05/11/06/action-figures-7243788_960_720.jpg');

        // Add data to pivot table
        $movie->addActor($this->getReference('actor_4'));
        $movie->addActor($this->getReference('actor_5'));
        $movie->addActor($this->getReference('actor_6'));

        $manager->persist($movie);

        $movie2 = new Movie();
        $movie2->setTitle('Batman: The Dark Knight');
        $movie2->setDescription('When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests of his ability to fight injustice.');
        $movie2->setReleaseYear(2008);
        $movie2->setCreatedAt($currentDate);
        $movie2->setImagePath('https://cdn.pixabay.com/photo/2021/02/17/09/39/batman-6023672_960_720.jpg');

        $movie2->addActor($this->getReference('actor_1'));
        $movie2->addActor($this->getReference('actor_2'));
        $movie2->addActor($this->getReference('actor_3'));

        $manager->persist($movie2);

        $manager->flush();
    }
}
