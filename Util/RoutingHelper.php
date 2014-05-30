<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\Util;

use Allegro\SitesBundle\Entity\Page;
use Allegro\SitesBundle\Entity\Site;
use Allegro\SitesBundle\Util\ConfigReader;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * A basic helper for generating URLs for entities
 */
class RoutingHelper
{
    protected $router;
    protected $session;
    protected $em;
    protected $absoluteUrls;
    protected $defaultLocale;

    public function __construct(Router $router, Session $session, EntityManager $em, $urlsFormat, $defaultLocale)
    {
        $this->router = $router;
        $this->session = $session;
        $this->em = $em;
        $this->absoluteUrls = $urlsFormat === 'absolute';
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Generates the url for the specified entity, routing string or page id
     *
     * @param mixed $entity Page | Site | string (routing name) | int (Page.id)
     * @param array $params associative array of parameters, used only when $entity is a routing name
     *
     * @return string The routing URL for accesing the entity
     * @internal parameter _locale doesn't need to be sent as param
     */
    public function generateUrl($entity, $params = null)
    {
        $_locale = $this->session->get('_locale', $this->defaultLocale);

        $defParams = array(
                'site' => $this->session->get('site'),
                '_locale' => $_locale,
            );

        $params = null === $params
                ? $defParams
                : array_merge($defParams, $params);

        // $entity = page id
        if (gettype($entity) === 'integer') {
            $page = $this->em->getRepository('AllegroSitesBundle:Page')->find($entity);
            if ($page) {
                return $this->generateUrl($page);
            }
        }

        // $entity = routing string
        if (gettype($entity) === 'string') {
            return $this->router->generate(
                $entity,
                $params,
                $this->absoluteUrls
            );
        }

        if ($entity instanceof Page) {
            $translation = $entity->getTranslationByLang($params['_locale']);

            if ($entity->getType() === 'p') {

                $params['site'] = $entity->getSite()->getSlug();
                $params['page'] = $translation->getSlug();
                return $this->router->generate(
                        'AllegroSites_page',
                        $params,
                        $this->absoluteUrls
                );
            }

            if ($entity->getType() === 'l') {
                $route = trim($translation->getBody());

                // has protocol => external
                if (preg_match('/^(https?|ftps?|javascript|mailto):/', $route)) {
                    return $route;
                }

                $baseRoute = $this->router->generate(
                    'AllegroSites_landingpage',
                    array(
                        'site' => $entity->getSite()->getSlug(),
                        '_locale' => $params['_locale']
                    ),
                    $this->absoluteUrls);

                return "{$baseRoute}{$route}";
            }
        }

        throw new RouteNotFoundException(sprintf(
                '[ASB] Unable to generate a URL for the (%s) entity "%s".',
                (gettype($entity) === 'object' ? get_class($entity) : gettype($entity)), $entity
        ));
    }

    /**
     * Generates the urls for the specified entity for all configured translations
     *
     * @param $entity Page | Site
     *
     * @return array('ĺocale' => 'url')
     */
    public function generateUrls($entity)
    {
        if ($entity instanceof Page) {
            $isAbsoluteUrl = ConfigReader::isAbsoluteUrl();
            $site = $entity->getSite()->getSlug();
            $routes = array();

            foreach ($entity->getTranslations() as $translation) {
                $locale = $translation->getLang();
                $title = $translation->getSlug();

                $routes[$locale] = $this->router->generate(
                    'AllegroSites_page',
                    array('site' => $site, '_locale' => $locale, 'page' => $title),
                    $isAbsoluteUrl
                );
            }

            return $routes;
        }

        if ($entity instanceof Site) {
            $routes = array();
            $locales = $entity->getAllTranslations();
            $isAbsoluteUrl = ConfigReader::isAbsoluteUrl();

            foreach ($locales as $locale) {
                $routes[$locale] = $this->router->generate(
                    'AllegroSites_sitemap',
                    array('_locale' => $locale, 'site' => $entity->getSlug()),
                    $isAbsoluteUrl
                );
            }

            return $routes;
        }

        throw new \InvalidArgumentException('[ASB] The (' . gettype($entity) . ') entity "' . $entity . '" is not supported');
    }
}
