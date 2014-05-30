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
 * @ORM\Entity(repositoryClass="Allegro\SitesBundle\Repository\PageRepository")
 * @ORM\Table(name="allegro_page_translation", uniqueConstraints={
 *     @UniqueConstraint(name="page_translation", columns={"page_id", "lang"}),
 *  })
 * @ORM\HasLifecycleCallbacks()
 */
class PageTranslation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="translations")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $page;

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
     * @ORM\JoinColumn(name="mod_author_id", referencedColumnName="id", nullable=true)
     */
    protected $modifiedBy;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $lastModified;

    /**
     * @ORM\Column(type="string", length=2, nullable=false)
     * @Assert\Length(min=2)
     */
    protected $lang;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $slug;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $body;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $seoMainKeyword;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $seoTitle;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(max=156)
     */
    protected $seoMetaDescription;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $seoFollowLinks;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $seoAddAuthor;

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    public function __toString()
    {
        return (empty($this->id))
            ? 'New page translation'
            : $this->getTitle() . ' (' . $this->getLang() . ')';
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
     * Set page
     *
     * @param \Allegro\SitesBundle\Entity\Page $page
     * @return PageTranslation
     */
    public function setPage(\Allegro\SitesBundle\Entity\Page $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return \Allegro\SitesBundle\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set createdBy
     *
     * @param \Application\Sonata\UserBundle\Entity\User $createdBy
     * @return PageTranslation
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
     * Set created
     *
     * @param \DateTime $created
     * @return PageTranslation
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
     * Set modifiedBy
     *
     * @param \Application\Sonata\UserBundle\Entity\User $modifiedBy
     * @return PageTranslation
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

    /**
     * Set lastModified
     *
     * @param \DateTime $lastModified
     * @return PageTranslation
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
     * @return PageTranslation
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
     * Set title
     *
     * @param string $title
     * @return PageTranslation
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return PageTranslation
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return PageTranslation
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
     * Set body
     *
     * @param string $body
     * @return PageTranslation
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set seoMainKeyword
     *
     * @param string $seoMainKeyword
     * @return PageTranslation
     */
    public function setSeoMainKeyword($seoMainKeyword)
    {
        $this->seoMainKeyword = $seoMainKeyword;

        return $this;
    }

    /**
     * Get seoMainKeyword
     *
     * @return string 
     */
    public function getSeoMainKeyword()
    {
        return $this->seoMainKeyword;
    }

    /**
     * Set seoTitle
     *
     * @param string $seoTitle
     * @return PageTranslation
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;

        return $this;
    }

    /**
     * Get seoTitle
     *
     * @return string 
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * Set seoMetaDescription
     *
     * @param string $seoMetaDescription
     * @return PageTranslation
     */
    public function setSeoMetaDescription($seoMetaDescription)
    {
        $this->seoMetaDescription = $seoMetaDescription;

        return $this;
    }

    /**
     * Get seoMetaDescription
     *
     * @return string 
     */
    public function getSeoMetaDescription()
    {
        return $this->seoMetaDescription;
    }

    /**
     * Set seoFollowLinks
     *
     * @param boolean $seoFollowLinks
     * @return PageTranslation
     */
    public function setSeoFollowLinks($seoFollowLinks)
    {
        $this->seoFollowLinks = $seoFollowLinks;

        return $this;
    }

    /**
     * Get seoFollowLinks
     *
     * @return boolean 
     */
    public function getSeoFollowLinks()
    {
        return $this->seoFollowLinks;
    }

    /**
     * Set seoAddAuthor
     *
     * @param boolean $seoAddAuthor
     * @return PageTranslation
     */
    public function setSeoAddAuthor($seoAddAuthor)
    {
        $this->seoAddAuthor = $seoAddAuthor;

        return $this;
    }

    /**
     * Get seoAddAuthor
     *
     * @return boolean 
     */
    public function getSeoAddAuthor()
    {
        return $this->seoAddAuthor;
    }
}
