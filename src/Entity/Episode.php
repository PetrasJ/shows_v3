<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity
 * @ORM\Table(name="episodes", indexes={
 *      @Index(name="episodeID", columns={"episode_id"}),
 *      @Index(name="showID", columns={"show_id"}),
 * })
 * @ORM\Entity(repositoryClass="App\Repository\EpisodeRepository")
 */
class Episode
{
    public $watchedOn;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $showID = 0;

    /**
     * @var Show
     * @ORM\ManyToOne(targetEntity="Show", inversedBy="episodes")
     * @ORM\JoinColumn(name="show_id", referencedColumnName="show_id")
     */
    private $show;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $episodeID = null;
    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */

    private $name = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $season = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $episode = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $airdate = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $airtime = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $duration = null;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $summary = null;

    /**
     * @param integer $showID
     *
     * @return Episode
     */
    public function setShowID($showID)
    {
        $this->showID = $showID;

        return $this;
    }

    /**
     * @return integer
     */
    public function getShowID()
    {
        return $this->showID;
    }

    /**
     * @param integer $episodeID
     *
     * @return Episode
     */
    public function setEpisodeID($episodeID)
    {
        $this->episodeID = $episodeID;

        return $this;
    }

    /**
     * @return integer
     */
    public function getEpisodeID()
    {
        return $this->episodeID;
    }

    /**
     * @param string $name
     *
     * @return Episode
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $season
     *
     * @return Episode
     */
    public function setSeason($season)
    {
        $this->season = $season;

        return $this;
    }

    /**
     * @return string
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * @param string $airdate
     *
     * @return Episode
     */
    public function setAirdate($airdate)
    {
        $this->airdate = $airdate;

        return $this;
    }

    /**
     * @return string
     */
    public function getAirdate()
    {
        return $this->airdate;
    }

    /**
     * @param string $airtime
     *
     * @return Episode
     */
    public function setAirtime($airtime)
    {
        $this->airtime = $airtime;

        return $this;
    }

    /**
     * @return string
     */
    public function getAirtime()
    {
        return $this->airtime;
    }

    /**
     * @param string $summary
     *
     * @return Episode
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $duration
     *
     * @return Episode
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return string
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param string $episode
     *
     * @return Episode
     */
    public function setEpisode($episode)
    {
        $this->episode = $episode;

        return $this;
    }

    /**
     * @return string
     */
    public function getEpisode()
    {
        return $this->episode;
    }

    /**
     * @return Show
     */
    public function getShow(): Show
    {
        return $this->show;
    }

    /**
     * @param Show $show
     * @return Episode
     */
    public function setShow(Show $show): Episode
    {
        $this->show = $show;

        return $this;
    }
}
