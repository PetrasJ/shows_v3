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
    private $defaultOffset = 0;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
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
    private $theme;

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
     * @return array
     */
    public function getCalendarShow(): ?array
    {
        return $this->calendarShow;
    }

    /**
     * @param array $calendarShow
     * @return User
     */
    public function setCalendarShow(array $calendarShow): User
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
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     * @return User
     */
    public function setTheme(string $theme): User
    {
        $this->theme = $theme;

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
