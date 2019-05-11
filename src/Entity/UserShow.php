<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_shows")
 * @ORM\Entity(repositoryClass="App\Repository\UserShowRepository")
 */
class UserShow
{
    const STATUS_WATCHING = null;
    const STATUS_WATCH_LATER = 1;
    const STATUS_ARCHIVED = 2;

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
     * @var Show
     * @ORM\OneToOne(targetEntity="Show")
     * @ORM\JoinColumn(name="show_id", referencedColumnName="id")
     */
    private $show;
        
    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */

    private $status;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $offset;


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
     * @return UserShow
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
}
