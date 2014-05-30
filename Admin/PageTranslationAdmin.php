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
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class PageTranslationAdmin extends Admin
{
    protected function configureRoutes(RouteCollection $routes)
    {
        $routes->clear();
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** 
         * this works patching sonata admin
         * @see https://github.com/jschmahl/AdminBundle/commit/703598c362e47168421b23d1f2961f5067588e59
         */
        /* @var $subject \Allegro\SitesBundle\Entity\PageTranslation */
        $subject = $this->getSubject();
        $isMain = $subject->getPage()->getMainTranslation()->getLang() === $subject->getLang();

        $formMapper
            ->with('Overview')
                ->add('lang', 'allg_static_text', array(
                    'required' => false,
                    'disabled' => true,
                    'label' => 'Language',
                    'data' => $subject->getLang() . ($isMain ? ' *' : ''),
                ))
                ->add('infoTitle', 'allg_static_text', array(
                    'required' => false,
                    'mapped' => false,
                    'label' => 'Title',
                    'data' => $subject->getTitle(),
                ))
                ->add('infoDescription', 'allg_static_text', array(
                    'required' => false,
                    'mapped' => false,
                    'label' => 'Description',
                    'data' => $subject->getDescription(),
                ))
                ->add('infoSlug', 'allg_static_text', array(
                    'required' => false,
                    'mapped' => false,
                    'label' => 'Slug',
                    'data' => $subject->getSlug(),
                ))
            ->end()

            ->with('Main')
                ->add('title', 'text', array('attr' => array('onkeyup' => 'setSlug(this)')))
                ->add('slug', 'text', array(
                ))
                ->add('description', 'text', array(
                    'required' => false,
                ))
                ->add('body', 'textarea', array(
                    'required' => false,
                    'attr' => array('class' => 'page-code'),
                ))
            ->end()

            ->with('SEO')
                ->add('seoMainKeyword', null, array(
                    'required' => false,
                    'label' => 'Main Keyword',
                    'help' => 'Human understandable keyword for the page',
                ))
                ->add('seoTitle', null, array(
                    'required' => false,
                    'label' => 'Title',
                    'help' => 'Title shown in search engines',
                ))
                ->add('seoMetaDescription', 'textarea', array(
                    'required' => false,
                    'label' => 'Meta-description',
                    'help' => 'Description shown in search engines. 156 chars max.'
                ))
                ->add('seoFollowLinks', null, array(
//                    'label' => 'Follow links',
                    'help' => 'Allow robots to follow contained links',
                ))
                ->add('seoAddAuthor', null, array(
                    'label' => 'Insert author',
                    'help' => 'Add author reference meta-data'
                ))
            ->end()

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

    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
          ->with('title')
          ->assertLength(array('max' => 64))
          ->end()
        ;
    }
}
