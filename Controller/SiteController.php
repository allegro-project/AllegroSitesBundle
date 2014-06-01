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

        /* @var $site \Allegro\SitesBundle\Entity\Site */
        $site = $this->requestSite($site);

        if ($site instanceof \Symfony\Component\HttpFoundation\Response) {
            return $site;
        }

        // if lang is not supported redirect to the default one
        $langs = $site->getAllTranslations();
        if (!in_array($_locale, $langs)) {
            return $this->redirect(
                    $this->generateUrl('AllegroSites_sitemap', array(
                            'site' => $site->getSlug(),
                            '_locale' => $site->getMainTranslation()->getLang()
                        )
                ));
        }

        $routes = $this->getRoutingHelper()->generateUrls($site);

        return $this->render($this->getTemplate('Site:map.html'), array(
                'site' => $site,
                '_locale' => $_locale,
                'localeRoutes' => $routes,
            )
        );
    }
}
