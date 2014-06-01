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

use Allegro\SitesBundle\Form\DataTransformer\DateTimeToStringTransformer;
use Allegro\SitesBundle\Form\DataTransformer\UserToStringTransformer;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class PageAdmin extends Admin
{
    protected $serviceContainer;

    public function setServiceContainer($serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * Adds a custom collection template.
     * TODO: Better way to add template custom types?
     * @return type
     */
    public function getFormTheme()
    {
        return array_merge(
            parent::getFormTheme(),
            array('AllegroSitesBundle:Admin:widgets.html.twig')
        );
    }

    public function getParentAssociationMapping()
    {
        return 'site';
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('create_page', 'create/{parentId}');
        $collection->remove('batch');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        // block direct access
        if (!$this->getParent()) {
            throw new \Exception("Site has not been defined");
        }

        $listMapper
            ->add('mainTitle', null, array(
                'label' => 'Title'
            ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                )
            ))
            ->add('enabled')
            ->add('visible')
            ->add('parent')
            ->add('pageType')
            ->add('lastModified')
            ->add('modifiedBy')
        ;
        $listMapper->remove('batch');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('site')
            ->add('parent')
            ->add('createdBy')
            ->add('type')
            ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        // block direct access
        if (!$this->getParent()) {
            throw new \Exception("Site has not been defined");
        }

        $subject = $this->getSubject();

        // new
        if (null === $subject->getId()) {
            /* @var $site \Allegro\SitesBundle\Entity\Site */
            $site =  $this->getParent()->getSubject();

            /* @var $parent \Allegro\SitesBundle\Entity\Page */
            $parent = null;

            $parentId = +$this->getRequest()->get('parentId');
            if ($parentId > 0) {
                foreach ($site->getPages() as $page) {
                    if ($page->getId() === $parentId) {
                        $parent = $page;
                        break;
                    }
                }
            }

            $formMapper
                ->with('General')
                    ->add('parentSite', 'allg_static_link', array(
                        'mapped' => false,
                        'required' => false,
                        'data' => $site->getTitle(),
                        'label' => 'Site',
                        'route' => 'admin_allegro_sites_site_edit',
                        'params' => array('id' => $site->getId()),
                    ))
                    ->add('site', 'sonata_type_model_hidden', array(
                        'data_class' => null,
                        'data' => $site,
                    ))
                    ->add('parent', 'entity', array(
                        'class' => 'AllegroSitesBundle:Page',
                        'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use ($site) {
                            return $er->createQueryBuilder('page')
                                ->where('page.site = ' . $site->getId());
                        },
                        'required' => false,
                        'data' => $parent,
                        'attr' => array('class' => 'parent-list', 'data-hide-disabled' => true),
                        'empty_value' => '-'
                    ))
                    ->add('type', 'choice', array(
                        'choices' => array('p' => 'page', 'l' => 'link'),
//                        'expanded' => true,
                        'data' => 'p',
                        'attr' => array('onchange' => 'showPageTypeFields(this.value);')
                    ))
                    ->add('enabled', null, array('required' => false, 'help' => 'if unchecked, a 404 eror page will be shown instead'))
                    ->add('visible', null, array('required' => false, 'help' => 'if unchecked, the menu entry for this page wont be shown'))
                ->end()
                ->with('Contents')
                    ->add('mainLanguage', 'allg_static_text', array(
                        'required' => false,
                        'disabled' => true,
                        'data' => $site->getMainLang()
                    ))
                    ->add('title', 'text', array('required' => true, 'attr' => array('onkeyup' => 'setSlug(this)')))
                    ->add('slug', 'text', array('required' => true))
                    ->add('description', 'text', array('required' => false))
                    ->add('head', 'textarea', array('required' => false, 'attr' => array('class' => 'page-code')))
                    ->add('body', 'textarea', array('required' => false, 'attr' => array('class' => 'page-code')))
                ->end()
                ;
        }

        // update
        else {
            $formMapper
                ->with('General')
                    ->add('Site', 'allg_static_link', array(
                        'required' => false,
                        'mapped' => false,
                        'data' => $subject->getSite()->getTitle(),
                        'route' => 'admin_allegro_sites_site_edit',
                        'params' => array('id' => $subject->getSite()->getId()),
                    ))
                    ->add('parent', 'entity', array(
                        'class' => 'AllegroSitesBundle:Page',
                        'query_builder' => function($er) use ($subject) {
                            return $er->createQueryBuilder('page')
                                ->where('page.site = ' . $subject->getSite()->getId())
                                ->andWhere('page.id != ' . $subject->getId());
                        },
                        'required' => false,
                        'data' => $subject->getParent(),
                        'attr' => array('class' => 'parent-list', 'data-hide-disabled' => true)
                    ))

                    ->add('type', 'choice', array(
                        'choices' => array('p' => 'page', 'l' => 'link'),
//                            'expanded' => true,
                        'attr' => array('onchange' => 'showPageTypeFields(this.value);')
                    ))
                    ->add('enabled', null, array('required' => false, 'help' => 'if unchecked, a 404 eror page will be shown instead'))
                    ->add('visible', null, array('required' => false, 'help' => 'if unchecked, the menu entry for this page wont be shown'))
                ->end()

                ->with('Contents')
                    ->add('head', 'textarea', array(
                        'required' => false,
                        'attr' => array('class' => 'page-code'),
                    ))
                    ->add('translations', 'sonata_type_collection', array(
                            'required' => true,
                            'type_options' => array('delete' => false),
                        ),
                        array('edit' => 'inline', 'inline' => 'standard')
                    )
                ->end()

//                ->with('Advanced', array('collapsed' => true, 'description' => 'Yes, I want to play here'))
//                    ->add('site', null, array('help' => 'Move to another site'))
//                ->end()

                ->with('Info')
                    ->add($formMapper->create('created', 'allg_static_text', array(
                            'required' => false,
                            'disabled' => true,
                        ))
                        ->addViewTransformer(new DateTimeToStringTransformer('D, M d Y H:i:s'))
                    )
                    ->add($formMapper->create('createdBy', 'allg_static_text', array(
                            'required' => false,
                            'disabled' => true,
                        ))
                        ->addViewTransformer(new UserToStringTransformer())
                    )
                    ->add($formMapper->create('lastModified', 'allg_static_text', array(
                            'required' => false,
                            'disabled' => true,
                        ))
                        ->addViewTransformer(new DateTimeToStringTransformer('D, M d Y H:i:s'))
                    )
                    ->add($formMapper->create('modifiedBy', 'allg_static_text', array(
                            'required' => false,
                            'disabled' => true,
                        ))
                        ->addViewTransformer(new UserToStringTransformer())
                    )
                ->end()
                ;
        }
    }

    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
          ->with('mainTitle')
          ->assertLength(array('max' => 64))
          ->end()
        ;
    }
}
