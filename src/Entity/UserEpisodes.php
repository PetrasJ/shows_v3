<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_episodes",indexes={
 *      @Index(name="episodeID", columns={"episode_id"}),
 *      @Index(name="showID", columns={"show_id"}),
 * })
 * @ORM\Entity(repositoryClass="App\Repository\UserEpisodesRepository")
 */
class UserEpisodes
{
    const STATUS_UNWATCHED = null;
    const STATUS_WATCHED = 1;
    const STATUS_COMMENTED = 2;
    const MAX_RESULT = 100;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $userID;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */

    private $showID;
    /**
     * @ORM\Column(type="integer", nullable=true)
     */

    private $episodeID;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $airdate;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $airtime;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="integer", length=20, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @ORM\Version
     * @var \DateTime
     */
    protected $created;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $userID
     *
     * @return UserEpisodes
     */
    public function setUserID($userID)
    {
        $this->userID = $userID;

        return $this;
    }

    /**
     * @return integer
     */
    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * @param integer $showID
     *
     * @return UserEpisodes
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
     * @return UserEpisodes
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
     * @param string $comment
     *
     * @return UserEpisodes
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param \DateTime $created
     *
     * @return UserEpisodes
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param int $status
     *
     * @return UserEpisodes
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $airdate
     *
     * @return UserEpisodes
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
     * @return UserEpisodes
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
}
