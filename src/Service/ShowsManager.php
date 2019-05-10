<?php

namespace App\Service;

use App\Entity\Show;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\HttpKernel\KernelInterface;

class ShowsManager
{
    private $entityManager;
    private $appKernel;

    public function __construct(EntityManagerInterface $entityManager, KernelInterface $appKernel)
    {
        $this->entityManager = $entityManager;
        $this->appKernel = $appKernel;
    }

    public function load()
    {
        $client = new Client();

        for ($page = 0; $page <= 2; $page++) {
            $shows = json_decode($client->get(sprintf('http://api.tvmaze.com/shows?page=%d', $page))->getBody());
            $this->addShows($shows);
        }

        return true;
    }

    private function addShows($shows)
    {
        foreach ($shows as $show) {
            $this->addShow($show);
        }
    }

    private function addShow($show)
    {
        if (!$show->id) {
            return false;
        }

        if ($this->entityManager->getRepository(Show::class)->findBy(['showID' => $show->id])) {
            return false;
        };

        $newShow = new Show();
        $newShow->setShowID($show->id);
        $newShow->setName($show->name);
        $newShow->setUrl($show->url);
        $newShow->setOfficialSite($show->officialSite);
        $newShow->setRating($show->rating->average);
        $this->saveShowImage($show->image->original, $show->image->medium, $show->id);
        $newShow->setUpdated($show->updated);
        $newShow->setWeight($show->weight);
        $newShow->setStatus($show->status);
        $newShow->setPremiered($show->premiered);
        $newShow->setGenres(json_encode($show->genres));
        if (isset($show->image->original)) {
            $newShow->setImage($show->image->original);
        }
        if (isset($show->image->medium)) {
            $newShow->setImageMedium($show->image->medium);
        }
        $newShow->setSummary($show->summary);
        $this->entityManager->persist($newShow);
        $this->entityManager->flush();

        return true;
    }

    private function saveShowImage($imageUrl, $imageMediumUrl, $showID)
    {

        $dir = $this->appKernel->getProjectDir() . '/public/img/shows';
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        if (!is_dir($dir . '/medium')) {
            mkdir($dir . '/medium');
        }

        try {
            $newFilename = $dir . $showID . "." . pathinfo($imageUrl, PATHINFO_EXTENSION);
            copy($imageUrl, $newFilename);
        } catch (\Exception $e) {
            error_log(__METHOD__ .' fails: ' . $e->getMessage());
        }
        try {
            $newFilename = $dir . '/medium' . $showID . "." . pathinfo($imageMediumUrl, PATHINFO_EXTENSION);
            return copy($imageMediumUrl, $newFilename);
        } catch (\Exception $e) {
            error_log(__METHOD__ .' fails: ' . $e->getMessage());
        }

        return true;
    }
}