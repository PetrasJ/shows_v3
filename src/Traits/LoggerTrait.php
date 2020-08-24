<?php

namespace App\Traits;

use Psr\Log\LoggerInterface;

trait LoggerTrait
{
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     * @required
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function error(string $message, $context = [])
    {
        $this->logger->error($message, $context);
    }

    public function info(string $message, $context = [])
    {
        $this->logger->info($message, $context);
    }
}
