<?php

namespace App\Test\Controller;

use App\Entity\Tweet;
use App\Repository\TweetRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TweetControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private TweetRepository $repository;
    private string $path = '/tweet/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Tweet::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Tweet index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'tweet[id]' => 'Testing',
            'tweet[created_at]' => 'Testing',
            'tweet[text]' => 'Testing',
        ]);

        self::assertResponseRedirects('/tweet/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Tweet();
        $fixture->setId('My Title');
        $fixture->setCreated_at('My Title');
        $fixture->setText('My Title');

        $this->repository->add($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Tweet');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Tweet();
        $fixture->setId('My Title');
        $fixture->setCreated_at('My Title');
        $fixture->setText('My Title');

        $this->repository->add($fixture, true);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'tweet[id]' => 'Something New',
            'tweet[created_at]' => 'Something New',
            'tweet[text]' => 'Something New',
        ]);

        self::assertResponseRedirects('/tweet/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getId());
        self::assertSame('Something New', $fixture[0]->getCreated_at());
        self::assertSame('Something New', $fixture[0]->getText());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Tweet();
        $fixture->setId('My Title');
        $fixture->setCreated_at('My Title');
        $fixture->setText('My Title');

        $this->repository->add($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/tweet/');
    }
}
