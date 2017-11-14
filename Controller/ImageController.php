<?php

namespace Satoripop\CropImagesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Satoripop\CropImagesBundle\Entity\Image;
use Satoripop\CropImagesBundle\Form\ImageType;

/**
 * Image controller.
 *
 * @author Mohamed Racem Zouaghi <racem.zouaghi@satoripop.tn>
 */
class ImageController extends Controller
{

    private $class;

    public function init()
    {
        $this->class = $this->container->getParameter('sp_images.image_class');
    }

    /**
     * Lists all Image entities.
     *
     */
    public function indexAction()
    {
        $this->init();

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository($this->class)->findAll();

        return $this->render('SatoripopCropImagesBundle:Image:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Creates a new Image entity.
     *
     */
    public function createAction(Request $request)
    {
        $this->init();

        $entity = new $this->class();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('image_show', array('id' => $entity->getId())));
        }

        return $this->render('SatoripopCropImagesBundle:Image:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Image entity.
     *
     * @param Image $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Image $entity)
    {
        $form = $this->createForm(new ImageType($this->class, false), $entity, array(
            'action' => $this->generateUrl('image_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'form.create', 'attr' => array('class' => 'btn btn-primary'), 'translation_domain' => 'SatoripopCropImagesBundle'));

        return $form;
    }

    /**
     * Displays a form to create a new Image entity.
     *
     */
    public function newAction()
    {
        $this->init();

        $entity = new $this->class();
        $form = $this->createCreateForm($entity);

        return $this->render('SatoripopCropImagesBundle:Image:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Image entity.
     *
     */
    public function showAction($id)
    {
        $this->init();

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($this->class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Image entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SatoripopCropImagesBundle:Image:show.html.twig', array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),));
    }

    /**
     * Displays a form to edit an existing Image entity.
     *
     */
    public function editAction($id)
    {
        $this->init();

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($this->class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Image entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SatoripopCropImagesBundle:Image:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Image entity.
     *
     * @param Image $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Image $entity)
    {
        $form = $this->createForm(new ImageType($this->class, true), $entity, array(
            'action' => $this->generateUrl('image_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'form.update', 'attr' => array('class' => 'btn btn-primary'), 'translation_domain' => 'SatoripopCropImagesBundle'));

        return $form;
    }

    /**
     * Edits an existing Image entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $this->init();

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository($this->class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Image entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('image_edit', array('id' => $id)));
        }

        return $this->render('SatoripopCropImagesBundle:Image:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Image entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $this->init();

        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository($this->class)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Image entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('image'));
    }

    /**
     * Creates a form to delete a Image entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('image_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'form.delete', 'attr' => array('class' => 'btn btn-link btn-xs'), 'translation_domain' => 'SatoripopCropImagesBundle'))
            ->getForm();
    }
}
