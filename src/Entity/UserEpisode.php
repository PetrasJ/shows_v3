<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_episodes",indexes={
 *      @Index(name="showID", columns={"show_id"}),
 *      @Index(name="episodeID", columns={"episode_id"}),
 *      @Index(name="userShowID", columns={"user_show_id"}),
 *      @Index(name="userId_episodeID", columns={"user_id", "episode_id"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\UserEpisodeRepository")
 */
class UserEpisode
{
    public const STATUS_UNWATCHED = 0;
    public const STATUS_WATCHED = 1;
    public const STATUS_COMMENTED = 2;
    public const MAX_RESULT = 100;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var Show
     * @ORM\ManyToOne(targetEntity="Show")
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
     * @var Episode
     * @ORM\ManyToOne(targetEntity="Episode")
     * @ORM\JoinColumn(name="episode_id", referencedColumnName="id")
     */
    private $episode;

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
     * @Gedmo\Timestampable(on="create")
     * @var DateTime
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     * @var DateTime
     */
    protected $updated;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): UserEpisode
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
     * @return Episode
     */
    public function getEpisode(): Episode
    {
        return $this->episode;
    }

    /**
     * @param Episode $episode
     * @return UserEpisode
     */
    public function setEpisode(Episode $episode): UserEpisode
    {
        $this->episode = $episode;

        return $this;
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
     * @param DateTime $created
     *
     * @return UserEpisode
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return DateTime
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

    /**
     * @return DateTime
     */
    public function getUpdated(): DateTime
    {
        return $this->updated;
    }

    /**
     * @param DateTime $updated
     * @return UserEpisode
     */
    public function setUpdated(DateTime $updated): UserEpisode
    {
        $this->updated = $updated;

        return $this;
    }
}
