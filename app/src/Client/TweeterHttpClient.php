<?php

namespace App\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TweeterHttpClient
{
    private $client;
    private $baseUrl = 'https://api.twitter.com/2/';
    private string $accessToken;
    private $oAuth2Url = 'https://api.twitter.com/oauth2/token?grant_type=client_credentials';

    public function __construct(
        HttpClientInterface $client,
        string $tweeterApiKey,
        string $tweeterSecretApiKey
    ) {
        $this->client = $client;

        $this->fetchOAuth2Token($tweeterApiKey, $tweeterSecretApiKey);
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $url = $this->baseUrl . $url;

        $options['auth_bearer'] =  $this->accessToken;

        $response = $this->client->request(
            $method,
            $url,
            $options
        );

        return $response;
    }

    private function fetchOAuth2Token(string $apiKey, string $secretApiKey): void
    {
        $response = $this->client->request(
            "POST",
            $this->oAuth2Url,
            [
                'auth_basic' => [$apiKey, $secretApiKey],
            ]
        );

        if (200 !== $response->getStatusCode()) {
            var_dump($response->getContent());

            throw new \Exception('Response status code is different than expected.');
        }

        $responseArray = $response->toArray();

        $this->accessToken = $responseArray['access_token'];
    }
}
