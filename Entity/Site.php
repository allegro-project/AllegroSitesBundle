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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Allegro\SitesBundle\Repository\SiteRepository")
 * @ORM\Table(name="allegro_site",uniqueConstraints={@ORM\UniqueConstraint(name="uq_slug", columns={"slug"})})
 * @ORM\HasLifecycleCallbacks()
 */
class Site
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="SiteTranslation", mappedBy="site", cascade={"persist", "remove"})
     */
    protected $translations;

    /**
     * @ORM\OneToOne(targetEntity="SiteTranslation", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="main_translation_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $mainTranslation;

    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="site", cascade={"persist", "remove"})
     * @ORM\OrderBy({"pageOrder" = "ASC"})
     */
    protected $pages;

    /**
     * @ORM\OneToOne(targetEntity="Page")
     * @ORM\JoinColumn(name="landing_page_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $landingPage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $permanentLandingRedirect;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $slug;

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
     * @ORM\Column(type="boolean")
     */
    protected $enabled = true;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $contactEmail;


    // Virtual fields

    /* When creating a new obj from admin, this will
     obtain the main lang string like 'en' or 'es' */
    protected $mainLanguage;
    public function getMainLanguage()
    {
        return $this->mainLanguage;
    }

    public function setMainLanguage($mainLanguage)
    {
        $this->mainLanguage = $mainLanguage;
    }

    /* When creating a new obj from admin, this will
     obtain the site description for the main lang */
    protected $description;
    public function getDescription()
    {
        return $this->description;
    }
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /* When creating a new obj from admin, this will
     obtain a strings array e.g. array('es', 'en', 'fi') */
    protected $translationLanguages = array();
    public function getTranslationLanguages()
    {
        return $this->translationLanguages;
    }
    public function setTranslationLanguages($translationLanguages)
    {
        $this->translationLanguages = $translationLanguages;
    }

    /* When updating pages tree this will containt the new structure */
    protected $menuOrder;
    public function getMenuOrder() {
        return $this->menuOrder;
    }
    public function setMenuOrder($menuOrder)
    {
        $this->menuOrder = $menuOrder;
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    public function __toString()
    {
        return (empty($this->id))
            ? 'New site'
            : $this->getTitle();
    }

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->pages = new ArrayCollection();
    }

    /**
     * Gets the lenguage set in the main translation for this site
     * 
     * @return string The language
     */
    public function getMainLang() {
        return null !== $this->getMainTranslation()
            ? $this->getMainTranslation()->getLang()
            : 'n/a';
    }

    /**
     * Obtains a string array with the available translations for the current
     * site. The first element is the main translation.
     * 
     * @return array array('xx', 'yy', 'zz')
     */
    public function getAllTranslations() {
        $main = $this->getMainTranslation()->getLang();
        $locales = array($main);
        foreach ($this->getTranslations() as $translation) {
            $lang = $translation->getLang();
            if ($lang !== $main) {
                $locales[] = $lang;
            }
        }

        return $locales;
    }

    /**
     * Obtains the site pages without parent.
     * 
     * @return \Doctrine\Common\Collections\Collection pages sorted by configured order
     */
    public function getFirstPages() {
        $pages = array();
        foreach ($this->pages as $page) {
            if (null === $page->getParent()) {
                $pages[] = $page;
            }
        }

        return new \Doctrine\Common\Collections\ArrayCollection($pages);
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
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
     * Set permanentLandingRedirect
     *
     * @param string $permanentLandingRedirect
     * @return Site
     */
    public function setPermanentLandingRedirect($permanentLandingRedirect)
    {
        $this->permanentLandingRedirect = $permanentLandingRedirect;

        return $this;
    }

    /**
     * Get permanentLandingRedirect
     *
     * @return string
     */
    public function getPermanentLandingRedirect()
    {
        return $this->permanentLandingRedirect;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Site
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
     * @return Site
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
     * Set created
     *
     * @param \DateTime $created
     * @return Site
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
     * @return Site
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
     * Set enabled
     *
     * @param boolean $enabled
     * @return Site
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Add pages
     *
     * @param \Allegro\SitesBundle\Entity\Page $pages
     * @return Site
     */
    public function addPage(\Allegro\SitesBundle\Entity\Page $pages)
    {
        $this->pages[] = $pages;

        return $this;
    }

    /**
     * Remove pages
     *
     * @param \Allegro\SitesBundle\Entity\Page $pages
     */
    public function removePage(\Allegro\SitesBundle\Entity\Page $pages)
    {
        $this->pages->removeElement($pages);
    }

    /**
     * Get pages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Set createdBy
     *
     * @param \Application\Sonata\UserBundle\Entity\User $createdBy
     * @return Site
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
     * @return Site
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
     * Add translations
     *
     * @param \Allegro\SitesBundle\Entity\SiteTranslation $translations
     * @return Site
     */
    public function addTranslation(\Allegro\SitesBundle\Entity\SiteTranslation $translations)
    {
        $this->translations[] = $translations;

        return $this;
    }

    /**
     * Remove translations
     *
     * @param \Allegro\SitesBundle\Entity\SiteTranslation $translations
     */
    public function removeTranslation(\Allegro\SitesBundle\Entity\SiteTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Set mainTranslation
     *
     * @param \Allegro\SitesBundle\Entity\SiteTranslation $mainTranslation
     * @return Site
     */
    public function setMainTranslation(\Allegro\SitesBundle\Entity\SiteTranslation $mainTranslation = null)
    {
        $this->mainTranslation = $mainTranslation;

        return $this;
    }

    /**
     * Get mainTranslation
     *
     * @return \Allegro\SitesBundle\Entity\SiteTranslation
     */
    public function getMainTranslation()
    {
        return $this->mainTranslation;
    }

    /**
     * Set landingPage
     *
     * @param \Allegro\SitesBundle\Entity\Page $landingPage
     * @return Site
     */
    public function setLandingPage(\Allegro\SitesBundle\Entity\Page $landingPage = null)
    {
        $this->landingPage = $landingPage;

        return $this;
    }

    /**
     * Get landingPage
     *
     * @return \Allegro\SitesBundle\Entity\Page
     */
    public function getLandingPage()
    {
        return $this->landingPage;
    }

    /**
     * Set contactEmail
     *
     * @param string $contactEmail
     * @return Site
     */
    public function setContactEmail($contactEmail)
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    /**
     * Get contactEmail
     *
     * @return string
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }
}
