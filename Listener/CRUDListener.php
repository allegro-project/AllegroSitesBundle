<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\Listener;

use Allegro\SitesBundle\Entity\Site;
use Allegro\SitesBundle\Entity\Page;
use Allegro\SitesBundle\Entity\SiteTranslation;
use Allegro\SitesBundle\Entity\PageTranslation;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CRUDListener
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    // <editor-fold defaultstate="collapsed" desc="pre-persists callers">
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Site && method_exists($this, 'prePersistSite')) {
            $this->prePersistSite($entity);
        }
        else if ($entity instanceof Page && method_exists($this, 'prePersistPage')) {
            $this->prePersistPage($entity);
        }
        else if ($entity instanceof SiteTranslation && method_exists($this, 'prePersistSiteTranslation')) {
            $this->prePersistSiteTranslation($entity);
        }
        else if ($entity instanceof PageTranslation && method_exists($this, 'prePersistPageTranslation')) {
            $this->prePersistPageTranslation($entity);
        }
    }// </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="pre-update callers">
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets();

        $entities = $uow->getScheduledEntityUpdates();
        foreach ($entities as $entity) {
            $ok = true;
            if ($entity instanceof Site && method_exists($this, 'preUpdateSite')) {
                $this->preUpdateSite($entity, $em);
            }
            else if ($entity instanceof Page && method_exists($this, 'preUpdatePage')) {
                $this->preUpdatePage($entity, $em);
            }
            else if ($entity instanceof SiteTranslation && method_exists($this, 'preUpdateSiteTranslation')) {
                $this->preUpdateSiteTranslation($entity, $em);
            }
            else if ($entity instanceof PageTranslation && method_exists($this, 'preUpdatePageTranslation')) {
                $this->preUpdatePageTranslation($entity, $em);
            }
            else {
                $ok = false;
            }

            if ($ok) {
                $em->persist($entity);
                $classMetadata = $em->getClassMetadata(get_class($entity));
                $em->getUnitOfWork()->recomputeSingleEntityChangeSet($classMetadata, $entity);
            }
        }
    }// </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="post-load callers">
    public function postLoad(LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();
        $entity = $args->getEntity();

        if ($entity instanceof Site && method_exists($this, 'postLoadSite')) {
            $this->postLoadSite($entity);
        }
        else if ($entity instanceof Page && method_exists($this, 'postLoadPage')) {
            $this->postLoadPage($em, $entity);
        }
        else if ($entity instanceof SiteTranslation && method_exists($this, 'postLoadSiteTranslation')) {
            $this->postLoadSiteTranslation($entity);
        }
        else if ($entity instanceof PageTranslation && method_exists($this, 'postLoadPageTranslation')) {
            $this->postLoadPageTranslation($entity);
        }
    }// </editor-fold>

    private function postLoadPage($em, Page $page) {
        $session = $this->container->get('session');
        $locale = $session->get('_locale');

        // manually loading translations in order to load default translation
        // the addTranslation method verifies that objects are only added once
        $translations = $em
                ->getRepository('Allegro\SitesBundle\Entity\PageTranslation')
                ->findBy(array('page' => $page->getId()));

        foreach ($translations as $translation) {
            $page->addTranslation($translation);
            if ($translation->getLang() === $locale) {
//                $page->setLanguage($translation->getLang());
//                $page->setTitle($translation->getTitle());
//                $page->setSlug($translation->getSlug());
//                $page->setDescription($translation->getDescription());
//                $page->setBody($translation->getBody());
            }
        }
    }

    private function prePersistSite(Site $site)
    {
        $date = new \DateTime();
        $author = null !== $site->getCreatedBy()
                ? $site->getCreatedBy()     // fixtures set the author directly, also
                : $this->getUser();         // getUser() isn't valid when loading fixtures
        $mainLocale = $site->getMainLanguage();

        $site->setCreated($date);
        $site->setLastModified($date);
        $site->setCreatedBy($author);          // well, it looks prettier this way
        $site->setModifiedBy($author);

        $locales = array_merge(
                array($mainLocale),
                $site->getTranslationLanguages()
        );

        foreach($locales as $locale) {
            $isMLoc = $locale === $mainLocale;

            $description =  (!$isMLoc ? '[' . strtoupper($locale) . '] ' : '')
                    . $site->getDescription();

            $siteTranslation = new SiteTranslation;
            $siteTranslation->setSite($site);
            $siteTranslation->setLang($locale);
            $siteTranslation->setCreated($date);
            $siteTranslation->setCreatedBy($author);
            $siteTranslation->setLastModified($date);
            $siteTranslation->setModifiedBy($author);
            $siteTranslation->setDescription($description);
            $site->addTranslation($siteTranslation);

            if ($isMLoc) {
                $site->setMainTranslation($siteTranslation);
            }
        }
    }

    /**
     * Updates the pages tree structure (parents-children relations) according
     * to the array structure received
     * 
     * @param \Allegro\SitesBundle\Entity\Site $site
     * @param array $pagesTree
     * @param \Doctrine\ORM\EntityManager $em
     * @param \Allegro\SitesBundle\Entity\Page $parent
     * @param array $pagesMap
     * @param int $order
     */
    private function setTreeStructure(Site $site, $pagesTree, EntityManager $em, Page $parent = null, $pagesMap = null, $order = 0) {
        if (null === $pagesMap) {
            $pagesMap = array();
            foreach ($site->getPages() as $page) {
                // removing children, parent will be overwritten
                foreach ($page->getChildren() as $child) {
                    $page->removeChildren($child);
                }
                $pagesMap[$page->getId()] = $page;
            }
        }

        $objectArray = $pagesTree[0];               // getting the real page array
        $uow = $em->getUnitOfWork();
        $md = $em->getClassMetadata('Allegro\SitesBundle\Entity\Page');
        foreach ($objectArray as $object) {
            /* @var $page \Allegro\SitesBundle\Entity\Page */
            $page = $pagesMap[$object['id']];

            $childrenList = $object['children']; 
            $childrenArray = $childrenList[0];      // same, only one element

            $page->setParent($parent);
            if (null === $parent) {
                $page->setPageOrder(++$order);
            }

            $uow->computeChangeSet($md, $page);     // mark page for update

            foreach ($childrenArray as $i => $child) {
                $child = $pagesMap[$child['id']];
                $child->setParent($page);
                $child->setPageOrder($i+1);
            }

            $this->setTreeStructure($site, $childrenList, $em, $page, $pagesMap, $order);
        }
    }

    private function preUpdateSite(Site $site, EntityManager $em)
    {
        if (null !== $site->getMenuOrder()) {
            $structure = json_decode($site->getMenuOrder(), true);
            if (is_array($structure)) {
                $this->setTreeStructure($site, $structure, $em);
            }
        }

        $site->setLastModified(new \DateTime());
        $site->setModifiedBy($this->getUser());
    }

    private function prePersistPage(Page $page)
    {
        $date = new \DateTime();
        $author = null !== $page->getCreatedBy()
                ? $page->getCreatedBy()        // fixtures set the author directly, also
                : $this->getUser();         // getUser() isn't valid when loading fixtures

        $mainLocale = $page->getSite()->getMainLang();
        $locales = $page->getSite()->getAllTranslations();

        $page->setCreated($date);
        $page->setLastModified($date);
        $page->setCreatedBy($author);
        $page->setModifiedBy($author);

        foreach($locales as $locale) {
            $isMLoc = $locale === $mainLocale;
            $title =  (!$isMLoc ? '[' . strtoupper($locale) . '] ' : '')
                    . $page->getTitle();
            $slug =  (!$isMLoc ? strtolower($locale) . '-' : '')
                    . $page->getSlug();

            $pageTranslation = new PageTranslation;
            $pageTranslation->setPage($page);
            $pageTranslation->setLang($locale);
            $pageTranslation->setCreatedBy($author);
            $pageTranslation->setModifiedBy($author);
            $pageTranslation->setCreated($date);
            $pageTranslation->setLastModified($date);
            $pageTranslation->setTitle($title);
            $pageTranslation->setSlug($slug);
            $pageTranslation->setSeoAddAuthor(false);
            $pageTranslation->setSeoFollowLinks(true);

            if ($isMLoc) {
                $pageTranslation->setDescription($page->getDescription());
                $pageTranslation->setBody($page->getBody());

                $page->setMainTranslation($pageTranslation);
            }

            $page->addTranslation($pageTranslation);
        }
    }


    private function preUpdatePage(Page $page, EntityManager $em)
    {
        $page->setLastModified(new \DateTime());
        $page->setModifiedBy($this->getUser());
    }


    /*
    private function prePersistSiteTranslation(SiteTranslation $siteTranslation)
    {
    }
    */


    private function preUpdateSiteTranslation(SiteTranslation $siteTranslation, EntityManager $em)
    {
        $siteTranslation->setLastModified(new \DateTime());
        $siteTranslation->setModifiedBy($this->getUser());
    }


    /*
    private function prePersistPageTranslation(PageTranslation $pageTranslation)
    {
    }
    */


    private function preUpdatePageTranslation(PageTranslation $pageTranslation, EntityManager $em)
    {
        $pageTranslation->setLastModified(new \DateTime());
        $pageTranslation->setModifiedBy($this->getUser());
    }


    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    private function getUser()
    {
        $secContext = $this->container->get('security.context');
        return $secContext->getToken()->getUser();
    }

    private function slugify($string)
    {
        // Remove accents from characters
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

        // Everything lowercase
        $string = strtolower($string);

        // Replace all non-word characters by dashes
        $string = preg_replace("/\W/", "-", $string);

        // Replace double dashes by single dashes
        $string = preg_replace("/-+/", '-', $string);

        // Trim dashes from the beginning and end of string
        $string = trim($string, '-');

        return $string;
    }
}
