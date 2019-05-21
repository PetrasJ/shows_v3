<?php

namespace App\EventListener;

use Doctrine\DBAL\Event\ConnectionEventArgs;

class  PdoListener
{
    public function postConnect(ConnectionEventArgs $args)
    {
        $args->getConnection()
            ->exec("SET time_zone = '+00:00'");
    }
}