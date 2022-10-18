<?php

namespace App\Service;

use App\Entity\Tweet;
use Doctrine\ORM\EntityManagerInterface;

class TweetCollection
{
    private EntityManagerInterface $entityManager;

    private array $tweets = [];

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function update(): void
    {
        $tweetRepository = $this->entityManager->getRepository(Tweet::class);

        foreach ($this->tweets as $tweet) {
            $tweetExists = $tweetRepository->count(['id' => $tweet->getId()]);

            if (!$tweetExists) {
                $this->entityManager->persist($tweet);
            }
        }

        $this->entityManager->flush();
    }

    public function add(Tweet $tweet): void
    {
        $this->tweets[] = $tweet;
    }

    public function clear(): void
    {
        $this->tweets = [];
    }
}
