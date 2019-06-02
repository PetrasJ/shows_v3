<?php

namespace App\EventListener;

use Doctrine\DBAL\Event\ConnectionEventArgs;
use Exception;

class PdoListener
{
    public function postConnect(ConnectionEventArgs $args)
    {
        try {
        $args->getConnection()
            ->exec("SET time_zone = '+00:00'");
        } catch (Exception $e) {

        }
    }
}
