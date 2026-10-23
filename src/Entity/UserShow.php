<?php

namespace App\Entity;

use App\Repository\UserShowRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;

#[Gedmo\SoftDeleteable]
#[ORM\Table(name: 'user_shows')]
#[ORM\Entity(repositoryClass: UserShowRepository::class)]
class UserShow
{
    public const STATUS_WATCHING = 0;
    public const STATUS_WATCH_LATER = 1;
    public const STATUS_ARCHIVED = 2;

    public const STATUS = [
        0 => 'watching',
        1 => 'watch later',
        2 => 'archived',
    ];

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var User
     */
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private $user;

    /**
     * @var Show
     */
    #[ORM\JoinColumn(name: 'show_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Show::class, fetch: 'EAGER')]
    private $show;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: UserEpisode::class, mappedBy: 'userShow')]
    private $userEpisodes;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $status;

    #[ORM\Column(type: 'integer', nullable: true, name: '`offset`')]
    private $offset;

    /**
     * @var DateTime|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $deletedAt;

    public function __construct()
    {
        $this->userEpisodes = new ArrayCollection();
    }

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

    public function setUser(UserInterface $user): UserShow
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
     * @return UserShow
     */
    public function setShow(Show $show): UserShow
    {
        $this->show = $show;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getUserEpisodes()
    {
        return $this->userEpisodes;
    }

    /**
     * @param ArrayCollection $userEpisodes
     * @return UserShow
     */
    public function setUserEpisodes(ArrayCollection $userEpisodes): UserShow
    {
        $this->userEpisodes = $userEpisodes;

        return $this;
    }

    /**
     * @param string $status
     *
     * @return UserShow
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param integer $offset
     *
     * @return UserShow
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return integer
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return DateTime|null
     */
    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    /**
     * @param DateTime|null $deletedAt
     * @return UserShow
     */
    public function setDeletedAt(?DateTime $deletedAt): UserShow
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
