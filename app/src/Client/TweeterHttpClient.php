<?php

namespace App\Client;

use App\Client\Exception\OAuthException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TweeterHttpClient
{
    private HttpClientInterface $client;
    private string $oAuth2Url = 'https://api.twitter.com/oauth2/token?grant_type=client_credentials';
    private string $baseUrl = 'https://api.twitter.com/2/';
    private string $accessToken;

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
        try {
            $response = $this->client->request(
                "POST",
                $this->oAuth2Url,
                [
                    'auth_basic' => [$apiKey, $secretApiKey],
                ]
            );
        } catch (\Exception $e) {
            throw new OAuthException('Error when calling token endpoint.', 0, $e);
        }

        if (200 !== $response->getStatusCode()) {
            throw new OAuthException('Response status code is different than expected.');
        }

        $token = $response->toArray();

        if (!array_key_exists('access_token', $token) || !is_string($token['access_token'])) {
            throw new OAuthException('Access token not found in token endpoint response.');
        }

        $this->accessToken = $token['access_token'];
    }
}
