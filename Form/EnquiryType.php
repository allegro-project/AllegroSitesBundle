<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EnquiryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label' => 'contact.form.name.label'
        ));
        $builder->add('email', 'email', array(
            'label' => 'contact.form.email.label'
        ));
        $builder->add('subject', 'text', array(
            'label' => 'contact.form.subject.label'
        ));
        $builder->add('body', 'textarea', array(
            'label' => 'contact.form.body.label'
        ));
        $builder->add('captcha', 'captcha', array(
            'label' => 'contact.form.captcha.label',
            'width' => 200,
            'height' => 40,
            'length' => 7,
            'quality' => 90,
            'background_color' => [100,100,100],
            'distortion' => false,
            'as_url' => true,
            'reload' => true,
        ));
    }

    public function getName()
    {
        return 'contact';
    }
}
