<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Allegro\SitesBundle\Entity\Page;

class PageRepository extends EntityRepository
{
    /**
     * Obtains the First level pages from site
     *
     * This method is used by MenuController
     * @param string $site
     */
    public function getParentPages($site)
    {
        $qb = $this->createQueryBuilder('p');
        $pages = $qb
                ->select('p')
                ->join('p.site', 's')
                ->where('p.parent is null')
                ->andwhere('p.enabled = true')
                ->andwhere('p.visible = true')
                ->andwhere('s.slug = :site')
                ->setParameter('site', $site)
                ->getQuery()
                ->getResult();

        return $pages;
    }

    /**
     * Returns the requested page translation entity
     *
     * @param string $site   The site slug
     * @param string $locale The locale id
     * @param string $page   The page slug
     *
     * @return mixed PageTranslation|null The page entity or null if not found
     */
    public function getPageTranslation($site, $locale, $page)
    {
        $repo = $this->getEntityManager()->getRepository('AllegroSitesBundle:PageTranslation');
        $qb = $repo->createQueryBuilder('t')
            ->select('t')
            ->join('t.page', 'p')
            ->join('p.site', 's')

            ->where('s.slug = :site')
            ->andwhere('t.lang = :locale')
            ->andwhere('t.slug = :page')
            ->andwhere('p.enabled = true')

            ->setParameter('site', $site)
            ->setParameter('page', $page)
            ->setParameter('locale', $locale);

        try {
            return $qb->getQuery()->getSingleResult();
        }
        catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * Returns an array with the breadcrumbs for the specified page
     *
     * @param Page $page
     * @param string $locale The locale id
     * @param $ret
     * @return array
     */
    public function getBreadcrumbs(Page $page, $locale, $ret = array())
    {
        $repo = $this->getEntityManager()->getRepository('AllegroSitesBundle:PageTranslation');
        $qb = $repo->createQueryBuilder('t')
                ->select('t')
                ->join('t.page', 'p')

                ->where('p.id = :id')
                ->andwhere('t.lang = :lang')

                ->setParameter('id', $page->getId())
                ->setParameter('lang', $locale);

        $entity = $qb->getQuery()->getSingleResult();
        if (count($entity) == 0) {
            return array();
        }

        $ret[] = $entity;

        $parent = $entity->getPage()->getParent();
        if (null !== $parent) {
            return $this->getBreadcrumbs($parent, $locale, $ret);
        }

        return array_reverse($ret);
    }
}
