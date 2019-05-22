<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity
 * @ORM\Table(name="episodes", indexes={
 *      @Index(name="showID", columns={"show_id"}),
 *      @Index(name="airstamp", columns={"airstamp"}),
 * })
 * @ORM\Entity(repositoryClass="App\Repository\EpisodeRepository")
 */
class Episode
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Show
     * @ORM\ManyToOne(targetEntity="Show", inversedBy="episodes")
     * @ORM\JoinColumn(name="show_id", referencedColumnName="id")
     */
    private $show;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */

    private $name;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $season;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $episode;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $airstamp;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $airdate;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $airtime;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $duration;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $summary;

    /**
     * @var DateTime
     */
    private $userAirstamp;

    /**
     * @var int
     */
    private $userStatus;

    /**
     * @var string
     */
    private $userComment;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Episode
     */
    public function setId($id): Episode
    {
        $this->id = $id;

        return $this;
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
     * @return DateTime
     */
    public function getAirstamp(): DateTime
    {
        return $this->airstamp;
    }

    /**
     * @param DateTime $airstamp
     * @return Episode
     */
    public function setAirstamp(DateTime $airstamp): Episode
    {
        $this->airstamp = $airstamp;

        return $this;
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

    /**
     * @return DateTime
     */
    public function getUserAirstamp(): DateTime
    {
        return $this->userAirstamp;
    }

    /**
     * @param DateTime $userAirstamp
     * @return Episode
     */
    public function setUserAirstamp(DateTime $userAirstamp): Episode
    {
        $this->userAirstamp = $userAirstamp;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserStatus(): int
    {
        return $this->userStatus;
    }

    /**
     * @param int $userStatus
     * @return Episode
     */
    public function setUserStatus(int $userStatus): Episode
    {
        $this->userStatus = $userStatus;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserComment(): string
    {
        return $this->userComment;
    }

    /**
     * @param string $userComment
     * @return Episode
     */
    public function setUserComment(string $userComment): Episode
    {
        $this->userComment = $userComment;
        return $this;
    }
}
