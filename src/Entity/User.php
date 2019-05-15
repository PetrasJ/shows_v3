<?php

namespace App\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $defaultOffset;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $calendarShow;

    /**
     * @var string
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $locale;

    /**
     * @var string
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $style;

    /**
     * @var string
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $timezone;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCalendarShow(): string
    {
        return $this->calendarShow;
    }

    /**
     * @param string $calendarShow
     * @return User
     */
    public function setCalendarShow(string $calendarShow): User
    {
        $this->calendarShow = $calendarShow;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultOffset(): ?int
    {
        return $this->defaultOffset;
    }

    /**
     * @param int $defaultOffset
     * @return User
     */
    public function setDefaultOffset(int $defaultOffset = null): User
    {
        $this->defaultOffset = $defaultOffset;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     * @return User
     */
    public function setLocale(string $locale): User
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getStyle(): string
    {
        return $this->style;
    }

    /**
     * @param string $style
     * @return User
     */
    public function setStyle(string $style): User
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     * @return User
     */
    public function setTimezone(string $timezone): User
    {
        $this->timezone = $timezone;

        return $this;
    }
}
