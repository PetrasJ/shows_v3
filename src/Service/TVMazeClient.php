<?php

namespace App\Service;

use App\Entity\Show;
use App\Traits\LoggerTrait;
use Exception;
use Psr\Log\LogLevel;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TVMazeClient
{
    use LoggerTrait;

    private const API_URL = 'http://api.tvmaze.com/shows';

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getEpisodes(Show $show): array
    {
        try {
            return json_decode($this->client
                ->request('GET', sprintf('%s/%d/episodes', self::API_URL, $show->getId()))
                ->getContent());
        } catch (Exception $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage());

            return [];
        }
    }

    public function getShows(int $page)
    {
        return json_decode($this->client
            ->request('GET', sprintf('%s?page=%d', self::API_URL, $page))
            ->getContent());
    }

    public function getShow(int $showId)
    {
        return json_decode($this->client
            ->request('GET', sprintf('%s/%d', self::API_URL, $showId))->getContent());
    }
}
