<?php

namespace App\Tests;

use App\Client\TweeterHttpClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TweeterHttpClientTest extends KernelTestCase
{
    const TOKEN = 'xyz';

    public function testRequest(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $callback = function ($method, $url, $options) {
            if (str_contains($url, '/oauth2/token')) {
                return new MockResponse(sprintf('{"access_token":"%s"}', self::TOKEN));
            } elseif (in_array('Authorization: Bearer ' . self::TOKEN, $options['headers'])) {
                return new MockResponse();
            }
        };

        $mockHttpClient = new MockHttpClient($callback);

        $container->set(HttpClientInterface::class, $mockHttpClient);

        $client = $container->get(TweeterHttpClient::class);
        $response = $client->request('GET', '');

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
