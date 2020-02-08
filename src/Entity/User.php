<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="there_is_already_an_account_with_this_email")
 * @ORM\Table(name="fos_user")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $emailConfirmationToken;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private $resetPasswordToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $resetPasswordRequestedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTime
     */
    private $lastLogin;

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
    private $theme = '';

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
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getEmailConfirmationToken()
    {
        return $this->emailConfirmationToken;
    }

    /**
     * @param ?string $emailConfirmationToken
     * @return User
     */
    public function setEmailConfirmationToken($emailConfirmationToken)
    {
        $this->emailConfirmationToken = $emailConfirmationToken;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return User
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResetPasswordToken()
    {
        return $this->resetPasswordToken;
    }

    /**
     * @param mixed $resetPasswordToken
     * @return User
     */
    public function setResetPasswordToken($resetPasswordToken)
    {
        $this->resetPasswordToken = $resetPasswordToken;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResetPasswordRequestedAt()
    {
        return $this->resetPasswordRequestedAt;
    }

    /**
     * @param mixed $resetPasswordRequestedAt
     * @return User
     */
    public function setResetPasswordRequestedAt($resetPasswordRequestedAt)
    {
        $this->resetPasswordRequestedAt = $resetPasswordRequestedAt;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    /**
     * @param DateTime $lastLogin
     * @return User
     */
    public function setLastLogin(DateTime $lastLogin): User
    {
        $this->lastLogin = $lastLogin;

        return $this;
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

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
