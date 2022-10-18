<?php

namespace App\Service;

use App\Entity\Tweet;
use App\Entity\User;
use App\Client\TweeterHttpClient;

class SpaceTweets
{
    private TweeterHttpClient $tweeterHttpClient;
    private UserCollection $userCollection;
    private TweetCollection $tweetCollection;

    /**
     * @param TweeterHttpClient $tweeterHttpClient
     * @param UserCollection $userCollection
     * @param TweetCollection $tweetCollection
     */
    public function __construct(TweeterHttpClient $tweeterHttpClient, UserCollection $userCollection, TweetCollection $tweetCollection)
    {
        $this->tweeterHttpClient = $tweeterHttpClient;
        $this->userCollection = $userCollection;
        $this->tweetCollection = $tweetCollection;
    }

    public function updateTweets(string $usernames): void
    {
        $this->fetchUsers($usernames);

        $this->userCollection->update();

        $this->fetchTweets();
    }

    public function fetchUsers(string $usernames): void
    {
        $response = $this->tweeterHttpClient->request(
            "GET",
            "users/by",
            [
                'query' => [
                    'usernames' => $usernames,
                ],
            ]
        );

        if (200 !== $response->getStatusCode()) {
            throw new \Exception('Response status code is different than expected.');
        }

        $responseData = json_decode($response->getContent(), true);

        foreach ($responseData['data'] as $userData) {
            $user = new User();
            $user->setId($userData['id']);
            $user->setName($userData['username']);

            $this->userCollection->add($user);
        }
    }

    private function fetchTweets(): void
    {
        foreach ($this->userCollection->getAll() as $user) {
            $this->fetchUserTweets($user);
            $this->tweetCollection->update();
        }
    }

    public function fetchUserTweets(User $user): void
    {
        $response = $this->tweeterHttpClient->request(
            "GET",
            sprintf("users/%d/tweets", $user->getId()),
            [
                'query' => [
                    'tweet.fields' => 'created_at',
                ],
            ]
        );

        if (200 !== $response->getStatusCode()) {
            throw new \Exception('Response status code is different than expected.');
        }

        $responseData = json_decode($response->getContent(), true);

        $this->tweetCollection->clear();

        foreach ($responseData['data'] as $tweetData) {
            $tweet = new Tweet();
            $tweet->setId($tweetData['id']);
            $tweet->setText($tweetData['text']);
            $tweet->setUser($user);

            $this->tweetCollection->add($tweet);
        }
    }
}
