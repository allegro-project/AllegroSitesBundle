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
 * @ORM\Entity(repositoryClass="Allegro\SitesBundle\Repository\PageRepository")
 * @ORM\Table(name="allegro_page")
 * @ORM\HasLifecycleCallbacks()
 */
class Page
{
    public function pageType() {
        switch($this->type) {
            case "p": return 'page';
            case "l": return 'link';
            default:  return 'asb_undefined_type_' . $this->type;
        }
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Site", inversedBy="pages")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $site;

    /**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="parent", cascade={"persist", "remove"})
     * @ORM\OrderBy({"pageOrder" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="PageTranslation", mappedBy="page", cascade={"persist", "remove"})
     */
    protected $translations;

    /**
     * @ORM\OneToOne(targetEntity="PageTranslation", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="main_translation_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $mainTranslation;

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
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastModified;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $head;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pageOrder;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $enabled = true;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $visible = true;

    // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

    // Virtual fields

    protected $mainLanguage;
    /**
     * When creating a new obj from admin, this will obtain the main lang
     * string like 'en' or 'es'
     */
    public function getMainLanguage()
    {
        return $this->mainLanguage;
    }

    public function setMainLanguage($mainLanguage)
    {
        $this->mainLanguage = $mainLanguage;
    }

    protected $title;
    public function getTitle()
    {
        return $this->title;
    }
    public function setTitle($title)
    {
        $this->title = $title;
    }

    protected $slug;
    public function getSlug()
    {
        return $this->slug;
    }
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    protected $description;
    public function getDescription()
    {
        return $this->description;
    }
    public function setDescription($description)
    {
        $this->description = $description;
    }

    protected $body;
    public function getBody()
    {
        return $this->body;
    }
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * used as column in the admin page list view
     */
    public function getMainTitle()
    {
        return $this->__toString();
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    /**
     * Associative array with lang as key and child as value
     * @var array 
     */
    protected $translationByLang = array();
    /**
     * Gets the translation corresponding to the lang sent as parameter
     * 
     * @param string $lang
     * @return PageTranslation
     */
    public function getTranslationByLang($lang) {
        if (empty($this->translations)) {
            return array();
        }

        else if (empty($this->translationByLang)) {
            foreach ($this->translations as $translation) {
                $this->translationByLang[$translation->getLang()] = $translation;
            }
        }

        return !array_key_exists($lang, $this->translationByLang)
                ? null
                : $this->translationByLang[$lang];
    }

    public function __toString()
    {
        if (null === $this->mainTranslation) {
            return 'New page';
        }

        return $this->getMainTranslation()->getTitle();
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
     * Set site
     *
     * @param \Allegro\SitesBundle\Entity\Site $site
     * @return Page
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
     * Set parent
     *
     * @param \Allegro\SitesBundle\Entity\Page $parent
     * @return Page
     */
    public function setParent(\Allegro\SitesBundle\Entity\Page $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Allegro\SitesBundle\Entity\Page
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param \Allegro\SitesBundle\Entity\Page $children
     * @return Page
     */
    public function addChildren(\Allegro\SitesBundle\Entity\Page $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Allegro\SitesBundle\Entity\Page $children
     */
    public function removeChildren(\Allegro\SitesBundle\Entity\Page $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getChildren($sorted = false)
    {
        if (!$sorted) {
            return $this->children;
        }

        $pagesArrays = array();
        foreach ($this->children as $child) {
            if (!isset($pagesArrays[$child->getPageOrder()])) {
                $pagesArrays[$child->getPageOrder()] = array($child);
            }
            else {
                $pagesArrays[$child->getPageOrder()][] = $child;
            }
        }
        $ret = array();
        foreach ($pagesArrays as $array) {
            $ret = array_merge($ret, $array);
        }
        return new \Doctrine\Common\Collections\ArrayCollection($ret);
    }

    /**
     * Add translations
     *
     * @param \Allegro\SitesBundle\Entity\PageTranslation $translations
     * @return Page
     */
    public function addTranslation(PageTranslation $translations)
    {
        // if initialized on constructor an error is thrown here
        if (null === $this->translations) {
            $this->translations = new ArrayCollection();
        }
        // avoiding duplicity created by the postload event
        if (!$this->translations->contains($translations)) {
            $this->translations[] = $translations;
        }

        return $this;
    }

    /**
     * Remove translations
     *
     * @param \Allegro\SitesBundle\Entity\PageTranslation $translations
     */
    public function removeTranslation(PageTranslation $translations)
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
     * @param \Allegro\SitesBundle\Entity\PageTranslation $mainTranslation
     * @return Page
     */
    public function setMainTranslation(\Allegro\SitesBundle\Entity\PageTranslation $mainTranslation = null)
    {
        $this->mainTranslation = $mainTranslation;

        return $this;
    }

    /**
     * Get mainTranslation
     *
     * @return \Allegro\SitesBundle\Entity\PageTranslation
     */
    public function getMainTranslation()
    {
        return $this->mainTranslation;
    }

    /**
     * Set createdBy
     *
     * @param \Application\Sonata\UserBundle\Entity\User $createdBy
     * @return Page
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
     * @return Page
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
     * @return Page
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
     * @return Page
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
     * Set head
     *
     * @param string $head
     * @return Page
     */
    public function setHead($head)
    {
        $this->head = $head;

        return $this;
    }

    /**
     * Get head
     *
     * @return string
     */
    public function getHead()
    {
        return $this->head;
    }

    /**
     * Set pageOrder
     *
     * @param integer $pageOrder
     * @return Page
     */
    public function setPageOrder($pageOrder)
    {
        $this->pageOrder = $pageOrder;

        return $this;
    }

    /**
     * Get pageOrder
     *
     * @return integer
     */
    public function getPageOrder()
    {
        return $this->pageOrder;
    }

    /**
     * Set type
     *
     * @param text $type
     * @return Page
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return text
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Page
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
     * Set visible
     *
     * @param boolean $visible
     * @return Page
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean
     */
    public function getVisible()
    {
        return $this->visible;
    }
}
