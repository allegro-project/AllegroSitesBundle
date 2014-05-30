<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class SiteTranslationAdmin extends Admin
{
    protected function configureRoutes(RouteCollection $routes)
    {
        $routes->clear();
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Translation')
                ->add('lang', 'allg_static_text', array('disabled' => true))
                ->add('description', null, array('required' => false))
            ->end();
    }
}
