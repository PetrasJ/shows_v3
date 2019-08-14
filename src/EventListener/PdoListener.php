<?php

namespace App\EventListener;

use App\Traits\LoggerTrait;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Exception;

class PdoListener
{
    use LoggerTrait;

    public function postConnect(ConnectionEventArgs $args)
    {
        try {
        $args->getConnection()
            ->exec("SET time_zone = '+00:00'");
        } catch (Exception $e) {
            $this->error($e->getMessage(), [__METHOD__ . ':' . __LINE__]);
        }
    }
}
