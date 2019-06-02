<?php

namespace App\Twig;

use App\Entity\UserEpisode;
use App\Entity\UserShow;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class EpisodeClassExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('class', [$this, 'class']),
        ];
    }

    public function class($episode)
    {
        $class = '';

        if ($episode['episodeStatus'] === UserEpisode::STATUS_WATCHED) {
            $class .= ' watched';
        }

        if ($episode['showStatus'] === UserShow::STATUS_WATCH_LATER) {
            $class .= ' watch-later';
        }

        if ($episode['showStatus'] === UserShow::STATUS_ARCHIVED) {
            $class .= ' archived';
        }

        return $class;
    }
}
