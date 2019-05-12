<?php

namespace App\Service;

use App\Entity\User;

class Storage
{
    /**
     * @var User
     */
    private $user;

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|object $user
     * @return Storage
     */
    public function setUser($user): Storage
    {
        $this->user = $user;

        return $this;
    }
}
