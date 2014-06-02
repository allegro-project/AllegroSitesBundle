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
use Allegro\SitesBundle\Entity\Enquiry;
use Allegro\SitesBundle\Form\EnquiryType;

/**
 * Controller for the Enquiry
 */
class EnquiryController extends BaseController
{
    /**
     * Shows contact page
     *
     * @param string $site The site (slug)
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function contactAction($site, $_locale = null)
    {
        /* @var $site \Allegro\SitesBundle\Entity\Site */
        $site = $this->requestSite($site);
        if ($site instanceof \Symfony\Component\HttpFoundation\Response) {
            return $site;
        }


        $langs = $site->getAllTranslations();
        // wrong locale redirect with the default one
        if (!in_array($_locale, $langs)) {
            return $this->redirect(
                    $this->generateUrl('AllegroSites_contact', array(
                            '_locale' => $site->getMainTranslation()->getLang()
                    ))
                );
        }

        $enquiry = new Enquiry();
        $form = $this->createForm(new EnquiryType(), $enquiry);
        $contactEmail = $site->getContactEmail();

        /* @var $validator \Symfony\Component\Validator\Validator */
        $validator = $this->get('validator');

        /* @var $errors \Symfony\Component\Validator\ConstraintViolationListInterface */
        $errors = $validator->validateValue($contactEmail, array(
            new \Symfony\Component\Validator\Constraints\Email(array('message' => 'Erroneous contact email structure')),
            new \Symfony\Component\Validator\Constraints\NotBlank(array('message' => 'Contact email has not been defined yet')),
        ));

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->get('session')->getFlashBag()->add('allegro_warning', $error->getMessage());
            }

            $form->remove('submit');
        }

        $request = $this->getRequest();
        if (count($errors) === 0 && $request->isMethod('post')) {
            $form->bind($request);

            if ($form->isValid()) {
                $emailFormat = $this->container->getParameter('allegro_sites.emails.contact.format');
                $message = \Swift_Message::newInstance()
                    ->setSubject(sprintf(
                        '[%s - Site Contact] %s',
                        $site->getSlug(),
                        $enquiry->getSubject()
                    ))

                    ->setFrom($contactEmail)
                    ->setTo($contactEmail)
                    ->setReplyTo($enquiry->getEmail())

                    ->setContentType('text/html', 'utf-8')
                    ->setBody($this->renderView(
                        $this->getTemplate('Enquiry:contactEmail' . $emailFormat),
                        array('enquiry' => $enquiry, 'lang' => $_locale)
                    )
                );

                $this->get('mailer')->send($message);

                $response = $this->get('translator')->trans('contact.form.response.success');
                $this->get('session')->getFlashBag()->add('allegro_success', $response);

                // Redirect to prevent users re-posting the form if they refresh the page
                return $this->redirect($this->generateUrl('AllegroSites_contact', array(
                        'site' => $site->getSlug(),
                        '_locale' => $_locale,
                    )));
            }
        }

        $routes = array();
        foreach ($langs as $lang) {
            $routes[$lang] = $this->generateUrl('AllegroSites_contact', array(
                    'site' => $site->getSlug(),
                    '_locale' => $lang
                ));
        }

        return $this->render($this->getTemplate('Enquiry:contact.html'), array(
            'site' => $site->getSlug(),
            '_locale' => $_locale,
            'localeRoutes' => $routes,
            'form' => $form->createView(),
        ));
    }
}
