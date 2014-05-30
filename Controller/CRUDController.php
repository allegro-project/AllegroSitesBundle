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

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CRUDController extends Controller
{
    /**
     * Return the Response object associated to the create action. This method
     * is defined to add the parent site id in the route. This makes it available
     * in the admin form through the request object.
     * 
     * @param string $parentId
     * @return Response
     * @throws AccessDeniedException
     */
    public function createPageAction($parentId)
    {
        return $this->createAction();
    }

    public function pagesTreeAction()
    {
        // the key used to lookup the template
        $templateKey = 'pages_tree';

        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        /**
         * This will hold the built form from custom *Admin. getForm() calls
         * configureFormFields() from the custom SiteAdmin class
         * 
         * @var \Symfony\Component\Form\Form $form
         */
        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod() == 'POST') {
            $form->submit($this->get('request'));

            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                
                // modify a field to trigger update (and crud listener)
                $object->setLastModified(new \DateTime());

                $this->admin->update($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array(
                        'result'    => 'ok',
                        'objectId'  => $this->admin->getNormalizedIdentifier($object)
                    ));
                }

                $this->addFlash('sonata_flash_success', $this->admin->trans(
                    'flash_edit_success',
                    array('%name%' => $this->admin->toString($object)),
                    'SonataAdminBundle'
                ));

                // redirect to tree
                return $this->redirect(
                    $this->generateUrl(
                        'admin_allegro_sites_site_pages_tree',
                        array('id' => $object->getId())
                ));
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash('sonata_flash_error', $this->admin->trans(
                        'flash_edit_error',
                        array('%name%' => $this->admin->toString($object)), 'SonataAdminBundle'
                    ));
                }
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'pages_tree',
            'form'   => $view,
            'object' => $object,
        ));
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException|\Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @param mixed $id
     *
     * @return Response|RedirectResponse
     */
    public function deleteAction($id)
    {
        $id     = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('DELETE', $object)) {
            throw new AccessDeniedException();
        }

        if ($this->getRestMethod() == 'DELETE') {
            // check the csrf token
            $this->validateCsrfToken('sonata.delete');

            try {
                $this->admin->delete($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array('result' => 'ok'));
                }

                $this->addFlash(
                    'sonata_flash_success',
                    $this->admin->trans(
                        'flash_delete_success',
                        array('%name%' => $this->admin->toString($object)),
                        'SonataAdminBundle'
                    )
                );

            } catch (ModelManagerException $e) {

                //get details of the PDO or DBAL exeption that was actually thrown
                $actualException = $e->getPrevious();
                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(
                        array(
                            'result' => 'error', 
                            'info' => $actualException->getMessage()
                        )
                    );
                }

                $this->addFlash(
                    'sonata_flash_error',
                    $this->admin->trans(
                        'flash_delete_error',
                        array('%name%' => $this->admin->toString($object)),
                        'SonataAdminBundle'
                    )
                );
                $this->addFlash(
                    'sonata_flash_error',
                    $actualException->getMessage()
                );
            }

            $refRequest = $this->get('request')->headers->get('referer');

            if (false !== strpos($refRequest, '?')) {
                $query = substr($refRequest, strpos($refRequest, '?')+1);
                parse_str($query, $params);

                if (key_exists('ref', $params)) {
                    if ('tree' === $params['ref']) {
                        $id = $this->get('request')->get($this->admin->getParent()->getIdParameter());
                        $redirectUrl = $this->admin->getParent()->generateUrl('pages_tree', array('id' => $id));
                    }
                }
            }

            if (!isset($redirectUrl)) {
                $redirectUrl = $this->admin->generateUrl('list');
            }

            return new RedirectResponse($redirectUrl);
        }

        return $this->render($this->admin->getTemplate('delete'), array(
            'object'     => $object,
            'action'     => 'delete',
            'csrf_token' => $this->getCsrfToken('sonata.delete')
        ));
    }
    /* */
}
