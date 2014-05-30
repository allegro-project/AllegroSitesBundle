<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\Controller;

use Allegro\SitesBundle\Controller\BaseController;

/**
 * Controller for the Menu
 */
class MenuController extends BaseController
{
    /**
     * Called from main layout twig template
     *
     * @return The rendered menu template
     */
    public function menuAction()
    {
        $site = $this->getSessionVar('site');
        /* @var $repo \Allegro\SitesBundle\Repository\SiteRepository */
        $repo = $this->getRepo('Site');

        /* @var $siteEntity Allegro\SitesBundle\Entity\Site */
        $siteEntity = $repo->getSiteBySlug($site);
        return $this->render($this->getTemplate('menu.html'), array(
            'site' => $siteEntity,
            '_locale' => $this->getSessionVar('_locale')
        ));
    }
}
