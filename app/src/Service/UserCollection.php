<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserCollection
{
    private EntityManagerInterface $entityManager;

    private array $users = [];

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function update(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);

        foreach ($this->users as &$user) {
            $userExists = $userRepository->find($user->getId());

            if (!$userExists) {
                $this->entityManager->persist($user);
            } else {
                $user = $userExists;
            }
        }

        $this->entityManager->flush();
    }

    public function add(User $user): void
    {
        $this->users[] = $user;
    }

    public function getAll(): array
    {
        return $this->users;
    }
}
