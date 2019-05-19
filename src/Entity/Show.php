<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="shows")
 * @ORM\Entity(repositoryClass="App\Repository\ShowRepository")
 */
class Show
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Episode", mappedBy="show")
     * @ORM\OrderBy({"airstamp" = "ASC"})
     */
    private $episodes;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */

    private $name;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $officialSite;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $rating;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $weight;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $imageMedium;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $summary;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $updated;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $premiered;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $genres;

    public function __construct()
    {
        $this->episodes = new ArrayCollection();
    }

    /**
     * @param int $id
     * @return Show
     */
    public function setId(int $id): Show
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return Show
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
     * @param string $url
     *
     * @return Show
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $officialSite
     *
     * @return Show
     */
    public function setOfficialSite($officialSite)
    {
        $this->officialSite = $officialSite;

        return $this;
    }

    /**
     * @return string
     */
    public function getOfficialSite()
    {
        return $this->officialSite;
    }

    /**
     * @param string $rating
     *
     * @return Show
     */
    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return string
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param string $image
     *
     * @return Show
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $imageMedium
     *
     * @return Show
     */
    public function setImageMedium($imageMedium)
    {
        $this->imageMedium = $imageMedium;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageMedium()
    {
        return $this->imageMedium;
    }

    /**
     * @param string $summary
     *
     * @return Show
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
     * @param integer $updated
     *
     * @return Show
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return integer
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return ArrayCollection
     */
    public function getEpisodes()
    {
        return $this->episodes;
    }

    /**
     * @param ArrayCollection $episodes
     * @return Show
     */
    public function setEpisodes(ArrayCollection $episodes): Show
    {
        $this->episodes = $episodes;

        return $this;
    }

    /**
     * @param string $status
     *
     * @return Show
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
     * @param string $premiered
     *
     * @return Show
     */
    public function setPremiered($premiered)
    {
        $this->premiered = $premiered;

        return $this;
    }

    /**
     * @return string
     */
    public function getPremiered()
    {
        return $this->premiered;
    }

    /**
     * @param string $genres
     *
     * @return Show
     */
    public function setGenres($genres)
    {
        $this->genres = $genres;

        return $this;
    }

    /**
     * @return string
     */
    public function getGenres()
    {
        return $this->genres;
    }

    /**
     * @param string $weight
     *
     * @return Show
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return string
     */
    public function getWeight()
    {
        return $this->weight;
    }
}
