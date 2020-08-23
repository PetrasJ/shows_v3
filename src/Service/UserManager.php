<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(UserInterface $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function getUserByEmailConfirmationToken(string $token): ?User
    {
        return $this->getRepository()->findOneBy(['emailConfirmationToken' => $token]);
    }

    public function getUserByResetPasswordToken(string $token): ?User
    {
        return $this->getRepository()->findOneBy(['resetPasswordToken' => $token]);
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->getRepository()->findOneBy(['email' => $email]);
    }

    private function getRepository(): UserRepository
    {
        return $this->entityManager->getRepository(User::class);
    }
}
