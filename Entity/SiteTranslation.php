<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Allegro\SitesBundle\Repository\SiteRepository")
 * @ORM\Table(name="allegro_site_translation", uniqueConstraints={
 *     @UniqueConstraint(name="site_translation", columns={"site_id", "lang"}),
 *  })
 * @ORM\HasLifecycleCallbacks()
 */
class SiteTranslation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Site", inversedBy="translations")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $site;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", nullable=false)
     */
    protected $createdBy;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $created;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="update_author_id", referencedColumnName="id", nullable=true)
     */
    protected $modifiedBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastModified;

    /**
     * @ORM\Column(type="string", length=2, nullable=false)
     * @Assert\Length(min=2)
     */
     protected $lang;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    public function __toString()
    {
        return (empty($this->id))
            ? 'New site translation'
            : $this->getDescription() . ' (' . $this->getLang() . ')';
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

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
     * Set created
     *
     * @param \DateTime $created
     * @return SiteTranslation
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set lastModified
     *
     * @param \DateTime $lastModified
     * @return SiteTranslation
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    /**
     * Get lastModified
     *
     * @return \DateTime
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Set lang
     *
     * @param string $lang
     * @return SiteTranslation
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return SiteTranslation
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set site
     *
     * @param \Allegro\SitesBundle\Entity\Site $site
     * @return SiteTranslation
     */
    public function setSite(\Allegro\SitesBundle\Entity\Site $site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return \Allegro\SitesBundle\Entity\Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set createdBy
     *
     * @param \Application\Sonata\UserBundle\Entity\User $createdBy
     * @return SiteTranslation
     */
    public function setCreatedBy(\Application\Sonata\UserBundle\Entity\User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set modifiedBy
     *
     * @param \Application\Sonata\UserBundle\Entity\User $modifiedBy
     * @return SiteTranslation
     */
    public function setModifiedBy(\Application\Sonata\UserBundle\Entity\User $modifiedBy)
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    /**
     * Get modifiedBy
     *
     * @return \Application\Sonata\UserBundle\Entity\User
     */
    public function getModifiedBy()
    {
        return $this->modifiedBy;
    }
}
