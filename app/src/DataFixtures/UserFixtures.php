<?php

namespace App\DataFixtures;

use App\Entity\Tweet;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setId(1);
        $user->setName('NASA');
        $manager->persist($user);

        $tweet = new Tweet();
        $tweet->setId(1);
        $tweet->setUser($user);
        $tweet->setText('lorem ipsum');
        $manager->persist($tweet);

        $manager->flush();
    }
}
