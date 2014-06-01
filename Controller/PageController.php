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
 * Controller for the Page
 */
class PageController extends BaseController
{
    /**
     * 
     * @param string $site slug
     * @param string $_locale
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function landingPageAction($site, $_locale = null)
    {
        /* @var $site \Allegro\SitesBundle\Entity\Site */
        $site = $this->requestSite($site);

        if ($site instanceof \Symfony\Component\HttpFoundation\Response) {
            return $site;
        }

        /* @var $landingPage Allegro\SitesBundle\Entity\Page */
        $landingPage = $site->getLandingPage();
        if (null !== $landingPage) {
            /* @var $translation Allegro\SitesBundle\Entity\PageTranslation */
            $translation = $landingPage->getTranslationByLang($_locale);
            if (null !== $translation) {
                $page = $translation->getSlug();
            }
            else {
                $page = $landingPage->getMainTranslation()->getSlug();
                $_locale = $site->getMainLang();
            }

            return $this->redirect(
                    $this->generateUrl('AllegroSites_page', array(
                        '_locale' => $_locale,
                        'site' => $site->getSlug(),
                        'page' => $page
                    )),
                    $site->getPermanentLandingRedirect()? 301 : 302
                );
        }

        return $this->redirect(
                $this->generateUrl('AllegroSites_sitemap', array(
                    'site' => $site->getSlug(),
                    '_locale' => !in_array($_locale, $site->getAllTranslations())
                                    ? $site->getMainLang()
                                    : $_locale
                )),
                $site->getPermanentLandingRedirect()? 301 : 302
            );
    }

    /**
     * Shows a page
     *
     * @param string $site    The site slug
     * @param string $_locale The locale id
     * @param string $page    The page slug
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException when page not found
     */
    public function showAction($site, $_locale, $page)
    {
        /* @var $repo \Allegro\SitesBundle\Repository\PageRepository */
        $repo = $this->getRepo('Page');
        /* @var $pageTranslation \Allegro\SitesBundle\Entity\PageTranslation */
        $pageTranslation = $repo->getPageTranslation($site, $_locale, $page);

        if (null === $pageTranslation) {
            $site = $this->requestSite($site);

            if ($site instanceof \Symfony\Component\HttpFoundation\Response) {
                return $site;
            }

            // if lang is not supported redirect to the default one
            if (!in_array($_locale, $site->getAllTranslations())) {
                return $this->redirect(
                        $this->generateUrl('AllegroSites_page', array(
                            'site' => $site->getSlug(),
                            '_locale' => $site->getMainLang(),
                            'page' => $page
                        ))
                    );
            }

            return $this->render($this->getTemplate('404_page.html'), array(
                    'resourceSlug' => $page,
                    'translation' => $pageTranslation,
                    'localeRoutes' => null
                    ),
                    new \Symfony\Component\HttpFoundation\Response('', 404)
            );
        }

        /* @var $pageEntity Allegro\SitesBundle\Entity\Page */
        $pageEntity = $pageTranslation->getPage();
        $routes = $this->getRoutingHelper()->generateUrls($pageEntity);
        $breadcrumbs = $repo->getBreadcrumbs($pageEntity, $_locale);
        $children = array();
        foreach ($pageEntity->getChildren(true) as $child) {
            $children[] = $child->getTranslationByLang($_locale);
        }

        return $this->render($this->getTemplate('Page:show.html'), array(
                'page' => $pageEntity,
                'translation' => $pageTranslation,
                'children' => $children,
                'localeRoutes' => $routes,
                'breadcrumbs' => $breadcrumbs,
        ));
    }
}
