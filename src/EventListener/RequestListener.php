<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\Storage;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RequestListener
{
    private $storage;
    private $tokenStorage;

    public function __construct(Storage $storage, TokenStorageInterface $tokenStorage)
    {
        $this->storage = $storage;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest()
    {
        if ($this->tokenStorage instanceof TokenStorageInterface
            && $this->tokenStorage->getToken() instanceof TokenInterface) {
            $user = $this->tokenStorage->getToken()->getUser();
            if ($user instanceof User) {
                $this->storage->setUser($user);
            }
        }
    }
}
