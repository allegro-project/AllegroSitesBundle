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

class SiteRepository extends EntityRepository
{
    /**
     * 
     * @param string $site Site slug
     * @return \Allegro\SitesBundle\Entity\Site|null
     */
    public function getSiteBySlug($site)
    {
        $qb = $this->createQueryBuilder('s');

        try {
            $site = $qb
                    ->select('s')
                    ->where('s.slug = :site')
                    ->setParameter('site', $site)
                    ->getQuery()
                    ->getSingleResult();

            return $site;
        }
        catch (NoResultException $e) {
            return null;
        }
    }
}
