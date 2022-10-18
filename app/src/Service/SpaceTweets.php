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
        $this->getUsers($usernames);

        $this->userCollection->update();

        $this->getTweets();
    }

    public function getUsers(string $usernames): void
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

        $responseJson = $response->getContent();
        $responseData = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);

        foreach ($responseData['data'] as $userData) {
            $user = new User();
            $user->setId($userData['id']);
            $user->setName($userData['username']);

            $this->userCollection->add($user);
        }
    }

    private function getTweets(): void
    {
        foreach ($this->userCollection->getAll() as $user) {
            $this->getUserTweets($user);
            $this->tweetCollection->update();
        }
    }

    public function getUserTweets(User $user): void
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

        $responseJson = $response->getContent();
        $responseData = json_decode($responseJson, true, 512, JSON_THROW_ON_ERROR);

        $this->tweetCollection->clear();

        foreach ($responseData['data'] as $datum) {
            $tweet = new Tweet();
            $tweet->setId($datum['id']);
            $tweet->setUser($user);
            $tweet->setText($datum['text']);

            $this->tweetCollection->add($tweet);
        }
    }
}
