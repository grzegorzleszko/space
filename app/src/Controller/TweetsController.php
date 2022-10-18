<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class TweetsController extends AbstractController
{
    #[Route('/api/tweets/{username}', name: 'app_tweets')]
    public function index(string $username, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['name' => $username]);

        if (!$user) {
            throw new NotFoundHttpException();
        }

        return $this->json($user->getTweets());
    }
}
