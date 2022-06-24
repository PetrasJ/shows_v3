<?php

namespace App\Service;

use App\Entity\Episode;
use App\Entity\Show;
use App\Traits\LoggerTrait;
use DateTime;
use DateTimeZone;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class EpisodeManager
{
    use LoggerTrait;

    private EntityManagerInterface $entityManager;
    private ?UserInterface $user;
    private TVMazeClient $client;

    public function __construct(EntityManagerInterface $entityManager, Security $security, TVMazeClient $client)
    {
        $this->entityManager = $entityManager;
        $this->user = $security->getUser();
        $this->client = $client;
    }

    public function addEpisodes(Show $show): int
    {
        $episodes = $this->client->getEpisodes($show);

        if ($episodes) {
            $this->removeEpisodes($show);
        }

        $saved = 0;
        foreach ($episodes as $episode) {
            if ($episode->name && $episode->airdate) {
                $this->entityManager->persist($this->makeEntity($show, $episode));
                $saved++;
            }
        }
        $this->entityManager->flush();

        return $saved;
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @param bool $watching
     * @param bool $excludeWatched
     * @return array
     */
    public function getEpisodes(DateTime $from, DateTime $to, $watching = false, $excludeWatched = false): ?array
    {
        if (!$this->user) {
            $episodes = $this->entityManager
                ->getRepository(Episode::class)
                ->getEpisodesPublic($from, $to)
            ;
        } else {
            $episodes = $this->entityManager
                ->getRepository(Episode::class)
                ->getEpisodes($from, $to, $this->user, $watching, $excludeWatched)
            ;
        }

        return $this->formatEpisodes($episodes);
    }

    private function formatEpisodes($episodes)
    {
        $formatted = [];
        if ($this->user) {
            $timezone = $this->user->getTimeZone() ?? 'UTC';
        } else {
            $timezone = 'UTC';
        }

        $timezone = new DateTimeZone($timezone);

        foreach ($episodes as $episode) {
            $date = new DateTime($episode['userAirstamp']);
            $date->setTimezone($timezone);
            $formatted[$date->format('Y-m-d')][] = $episode;
        }

        return $formatted;
    }

    /**
     * @param Show $show
     */
    private function removeEpisodes(Show $show): void
    {
        $episodes = $this->entityManager->getRepository(Episode::class)->findBy(['show' => $show]);

        foreach ($episodes as $episode) {
            $this->entityManager->remove($episode);
        }
        try {
            $this->entityManager->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
        } catch (\Exception $e) {
            $this->error($e->getMessage(), [__METHOD__]);
        }
        $this->entityManager->flush();
    }

    private function makeEntity(Show $show, \stdClass $episode): Episode
    {
        $dateTime = (new \DateTime())->createFromFormat('Y-m-d\TH:i:s\+00:00', $episode->airstamp);

        return (new Episode())
            ->setId($episode->id)
            ->setShow($show)
            ->setName($episode->name)
            ->setSeason($episode->season)
            ->setEpisode($episode->number)
            ->setAirstamp($dateTime)
            ->setAirdate($episode->airdate)
            ->setAirtime($episode->airtime)
            ->setSummary($episode->summary)
            ->setDuration($episode->runtime)
            ;
    }
}
