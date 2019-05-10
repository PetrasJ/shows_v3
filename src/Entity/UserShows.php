<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_shows")
 * @ORM\Entity(repositoryClass="App\Repository\UserShowsRepository")
 */
class UserShows
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
     * @ORM\Column(type="string", length=100, nullable=true)
     */

    private $showID;
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
     * @return UserShows
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
     * @param string $showID
     *
     * @return UserShows
     */
    public function setShowID($showID)
    {
        $this->showID = $showID;

        return $this;
    }

    /**
     * @return string
     */
    public function getShowID()
    {
        return $this->showID;
    }

    /**
     * @param string $status
     *
     * @return UserShows
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
     * @return UserShows
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
