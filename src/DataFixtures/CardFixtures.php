<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\Card;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CardFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        $user = new User();
        $user->setPseudo("Test")
             ->setEmail("test@gmail.com")
             ->setPassword("un mot de passe");

        $manager->persist($user);

        // création de 3 tags fakés, puis de 5 cards reliées
        for ($j = 1; $j <= 3; $j++) { 
            $tag = new Tag();
            $tag->setName($faker->word)
                ->setUser($user);

            $manager->persist($tag);

            for ($i = 1; $i <= 5; $i++) {
                $card = new Card();
                $card->setRecto($faker->sentence())
                     ->setVerso($faker->sentence())
                     ->setDateCreation($faker->dateTimeBetween('-3 days', 'now'))
                     ->setUser($user);
                    
                $now = new \DateTime();
                $interval = $now->diff($card->getDateCreation());
                $hours = $interval->h;
                $minimum = '-' . $hours . 'hours'; // ex. -2 hours

                $card->setDatePublication($faker->dateTimeBetween($minimum), '+2 days');
                $card->addTag($tag);

                $manager->persist($card);
            }
        }

        $manager->flush();
    }
}
