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
 * Controller for the Site
 */
class SiteController extends BaseController
{
    /**
     * Displays the site map.
     * If invalid or no locale received this will redirect with the default locale
     *
     * @param string $site    The site slug
     * @param string $_locale The locale id
     *
     * @return The rendered template
     */
    public function sitemapAction($site, $_locale = null)
    {
        if (null === $_locale) {
            // redirect using last locale used
            return $this->redirect($this->generateUrl('AllegroSites_sitemap'));
        }

        /* @var $siteEntity \Allegro\SitesBundle\Entity\Site */
        $siteEntity = $this->getRepo('Site')->getSiteBySlug($site);

        if (null === $siteEntity) {
            throw $this->createNotFoundException(
                sprintf('Site "%s" not found...ddfgruaaghkhhhsdfadf!', $site)
            );
        }

        // if lang is not supported redirect to the default one
        $langs = $siteEntity->getAllTranslations();
        if (!in_array($_locale, $langs)) {
            return $this->redirect(
                    $this->generateUrl('AllegroSites_sitemap', array(
                            '_locale' => $siteEntity->getMainTranslation()->getLang()
                        )
                ));
        }

        $routes = $this->getRoutingHelper()->generateUrls($siteEntity);

        return $this->render($this->getTemplate('Site:map.html'), array(
                'site' => $siteEntity,
                '_locale' => $_locale,
                'localeRoutes' => $routes,
            )
        );
    }
}
