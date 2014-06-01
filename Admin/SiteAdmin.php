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
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

class SiteAdmin extends Admin
{
    protected $serviceContainer;

    public function setServiceContainer($serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    public function getTemplate($name)
    {
        switch ($name) {
            // called from custom CRUD controller
            // TODO how to make this work in a service
            case 'pages_tree':
                return 'AllegroSitesBundle:CRUD:pages_tree.html.twig';
            case 'list__action_pages_tree':
                return 'AllegroSitesBundle:CRUD:list__action_pages_tree.html.twig';

            default:
                return parent::getTemplate($name);
        }
    }

    /**
     * Adds a custom collection template.
     * TODO: Better way to add ustom type template?
     * @return array
     */
    public function getFormTheme()
    {
        return array_merge(
            parent::getFormTheme(),
            array('AllegroSitesBundle:Admin:widgets.html.twig')
        );
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        // tree custion action defined in custom CRUD controller
        $collection->add('pages_tree', '{id}/page/tree');
        $collection->remove('batch');
    }

    /**
     * 
     * @param \Knp\Menu\ItemInterface $menu
     * @param type $action
     * @param \Sonata\AdminBundle\Admin\AdminInterface $childAdmin
     * @return type
     */
    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        // $actions come from controllers
        if (!$childAdmin && !in_array($action, array('edit', 'show', 'pages_tree'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        $menu->addChild('Edit', array('uri' => $admin->generateUrl('edit', array('id' => $id))));
        $menu->addChild('Tree', array('uri' => $admin->generateUrl('pages_tree', array('id' => $id))));
        $menu->addChild('List', array('uri' => $admin->generateUrl('allegro_sites.admin.page.list', array('id' => $id))));
        $menu->addChild('Details', array('uri' => $admin->generateUrl('show', array('id' => $id))));
        $menu->addChild('Add page', array('uri' => $admin->generateUrl('allegro_sites.admin.page.create', array('id' => $id))));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('title')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'pages_tree' => array(
                        'template' => $this->getTemplate('list__action_pages_tree')
                    ),
                    'show' => array(),
                )
            ))
            ->add('enabled')
            ->add('slug')
            ->add('created')
            ->add('createdBy')
            ->add('lastModified')
            ->add('modifiedBy')
        ;

        $listMapper->remove('batch');
     }

    protected function configureShowFields(ShowMapper $mapper)
    {
        $mapper
            ->with('General')
                ->add('title')
                ->add('slug')
                ->add('created')
                ->add('createdBy')
                ->add('lastModified')
                ->add('modifiedBy')
                ->add('id')
            ->end();
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $request = $this->getRequest();
        $route = $request->get('_route');
        $subject = $this->getSubject();

        // pages tree
        if ('admin_allegro_sites_site_pages_tree' === $route) {

            $formMapper
                ->with('Pages')
                    ->add('pagesTree', 'allg_site_tree', array(
                        'required' => false,
                        'mapped' => false,
                        'label' => false,
                        'by_reference' => true,
                        'data' => $subject,
                        'class' => 'Allegro\SitesBundle\Entity\Site',
                    ))
                    ->add('menuOrder', 'hidden', array())
                ->end()
            ;
            return;
        }

        // no id => create
        if (null == $subject->getId()) {

            $formMapper
                ->with('General')
                    ->add('title', null, array('attr' => array('onkeyup' => 'setSlug(this)')))
                    ->add('slug', null, array('required' => true))
                    ->add('enabled', null, array('required' => false))
                    ->add('contactEmail', 'email', array('required' => false))
                ->end()
                ->with('Languages')
                    // virtual fields
                    ->add('mainLanguage', 'text', array('max_length' => 2))
                    ->add('description', 'text', array('required' => false))
                    ->add('translationLanguages', 'collection', array(
                        'type' => 'text',
                        'required' => true,
                        'allow_add' => true,
                        'allow_delete' => true,
                        'prototype_name' => '__asb-nolabel__',
                        ))
                ->end()
                ;
        }

        // update
        else {
            $formMapper
                ->with('Manage')
                    ->add('title', null, array('attr' => array('onkeyup' => 'setSlug(this)')))
                    ->add('slug', null, array('required' => true))
                    ->add('enabled', null, array('required' => false))
//                ->end()
//                ->with('Languages')
                    ->add('mainLanguage', 'allg_static_text', array(
                        'required' => false,
                        'data' => $subject->getMainLang()
                    ))
                    ->add('translations', 'sonata_type_collection',
                        array(
                            'by_reference' => false,
                            'required' => false,
                            'type_options' => array('delete' => false),
                           ),
                        array(
                            'edit' => 'inline',
                            'inline' => 'table',
                        )
                    )
                    ->add('landingPage', null, array(
                        'required' => false,
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use ($subject) {
                            return $er->createQueryBuilder('page')
                                ->where('page.site = ' . $subject->getId());
                        },
                        'help' => 'Leave empty to redirect to site map',
                    ))
                    ->add('permanentLandingRedirect', null, array('help' => 'Tell the client to request the redirected URL automatically'))
                    ->add('contactEmail', 'email', array('required' => false))
                ->end()
                ;
        }
    }

    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
          ->with('title')
          ->assertLength(array('max' => 64))
          ->end()
        ;
    }
}
