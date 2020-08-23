<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserManager
{
    private $entityManager;

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
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['emailConfirmationToken' => $token]);
    }

    public function getUserByResetPasswordToken(string $token): ?User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['resetPasswordToken' => $token]);
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }
}
