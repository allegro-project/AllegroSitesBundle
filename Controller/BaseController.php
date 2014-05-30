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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Base controller with some methods
 */
class BaseController extends Controller
{
    protected $bundle = 'AllegroSitesBundle';

    /**
     * Finds the template corresponding checking first if there is a custom
     * template for it.
     * Custom templates are placed in same location as the default one and
     * with the site slug prepended, e.g. startupdigital_layout.html.twig when
     * overriding layout.html.twig
     * 
     * @param type $template
     * @return string
     */
    public function getTemplate($template)
    {
        $bundle = $this->bundle;
        $baseDir = 'base';
        $overrideDir = 'tpl_' . $this->getSessionVar('site');
        $ext = '.twig';

        $templateName = (false === strpos($template, ':') ? ':' : '/') . $template;

        $template = "{$bundle}:{$overrideDir}{$templateName}{$ext}";

        if (!$this->get('templating')->exists($template)) {
            $template = "{$bundle}:{$baseDir}{$templateName}{$ext}";
        }

        return $template;
    }

    /**
     * Shortcut function for getting a repository from this bundle
     *
     * @param string $entity the name of the entity, e.g. 'Page'
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepo($entity)
    {
        return $this->getDoctrine()->getManager()->getRepository($this->bundle . ':' . $entity);
    }

    /**
     * Shortcut function for getting a session variable
     */
    protected function getSessionVar($key)
    {
        return $this->getRequest()->getSession()->get($key);
    }

    /**
     * Gets the base routing helper defined as service
     *
     * @return the helper object
     */
    protected function getRoutingHelper()
    {
        return $this->get('allegro_sites.helper.routing');
    }
}




















