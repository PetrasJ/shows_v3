<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_episodes",indexes={
 *      @Index(name="showID", columns={"show_id"}),
 *      @Index(name="userShowID", columns={"user_show_id"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\UserEpisodeRepository")
 */
class UserEpisode
{
    const STATUS_UNWATCHED = 0;
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
     * @var User
     * @ORM\OneToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var Show
     * @ORM\OneToOne(targetEntity="Show")
     * @ORM\JoinColumn(name="show_id", referencedColumnName="id")
     */
    private $show;

    /**
     * @var UserShow
     * @ORM\ManyToOne(targetEntity="UserShow", inversedBy="userEpisodes")
     * @ORM\JoinColumn(name="user_show_id", referencedColumnName="id")
     */
    private $userShow;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $episodeID;

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
     * @var DateTime
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
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return UserEpisode
     */
    public function setUser(User $user): UserEpisode
    {
        $this->user = $user;

        return $this;
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
     * @return UserEpisode
     */
    public function setShow(Show $show): UserEpisode
    {
        $this->show = $show;

        return $this;
    }

    /**
     * @return UserShow
     */
    public function getUserShow(): UserShow
    {
        return $this->userShow;
    }

    /**
     * @param UserShow $userShow
     * @return UserEpisode
     */
    public function setUserShow(UserShow $userShow): UserEpisode
    {
        $this->userShow = $userShow;

        return $this;
    }

    /**
     * @param integer $episodeID
     *
     * @return UserEpisode
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
     * @return UserEpisode
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
     * @return UserEpisode
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
     * @return UserEpisode
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
}
