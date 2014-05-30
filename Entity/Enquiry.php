<?php

/*
 * This file is part of the Allegro package.
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Allegro\SitesBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Enquiry
{
    /**
    * @Assert\NotBlank(message="contact.form.name.message")
    */
    protected $name;

    /**
    * @Assert\Email(message="contact.form.email.message")
    */
    protected $email;

    /**
    * @Assert\Length(max="65", maxMessage="contact.form.subject.maxMessage")
    */
    protected $subject;

    /**
    * @Assert\Length(min="25", minMessage="contact.form.body.minMessage")
    */
    protected $body;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }
}
