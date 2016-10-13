<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Ziiweb\EcommerceBundle\Entity\Manufacturer;
use Ziiweb\EcommerceBundle\Form\ManufacturerType;

/**
 * Manufacturer controller.
 *
 * @Route("/manufacturer")
 */
class ManufacturerController extends Controller
{
    /**
     * Lists all Manufacturer entities.
     *
     * @Route("/", name="manufacturer_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $manufacturers = $em->getRepository('ZiiwebEcommerceBundle:Manufacturer')->findAll();

        return $this->render('manufacturer/index.html.twig', array(
            'manufacturers' => $manufacturers,
        ));
    }

    /**
     * Creates a new Manufacturer entity.
     *
     * @Route("/new", name="manufacturer_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $manufacturer = new Manufacturer();
        $form = $this->createForm('Ziiweb\EcommerceBundle\Form\ManufacturerType', $manufacturer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($manufacturer);
            $em->flush();

            return $this->render('ZiiwebEcommerceBundle:Manufacturer:edit.html.twig', array(
                'id' => $manufacturer->getId(),
                'edit_form' => $form->createView()
            ));
        }

        return $this->render('ZiiwebEcommerceBundle:Manufacturer:new.html.twig', array(
            'manufacturer' => $manufacturer,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Manufacturer entity.
     *
     * @Route("/{id}", name="manufacturer_show")
     * @Method("GET")
     */
    public function showAction(Manufacturer $manufacturer)
    {
        $deleteForm = $this->createDeleteForm($manufacturer);

        return $this->render('manufacturer/show.html.twig', array(
            'manufacturer' => $manufacturer,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Manufacturer entity.
     *
     * @Route("/{id}/edit", name="manufacturer_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Manufacturer $manufacturer)
    {
        $deleteForm = $this->createDeleteForm($manufacturer);
        $editForm = $this->createForm('Ziiweb\EcommerceBundle\Form\ManufacturerType', $manufacturer);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($manufacturer);
            $em->flush();

            return $this->render('ZiiwebEcommerceBundle:Manufacturer:edit.html.twig', array(
                'id' => $manufacturer->getId(),
                'edit_form' => $editForm->createView(),
            ));
        }

        return $this->render('ZiiwebEcommerceBundle:Manufacturer:edit.html.twig', array(
            'manufacturer' => $manufacturer,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Manufacturer entity.
     *
     * @Route("/delete/{id}", name="manufacturer_delete")
     */
    public function deleteAction(Request $request, Manufacturer $manufacturer)
    {
        $form = $this->createDeleteForm($manufacturer);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $em->remove($manufacturer);
        $em->flush();

        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer); 
    }

    /**
     * Creates a form to delete a Manufacturer entity.
     *
     * @param Manufacturer $manufacturer The Manufacturer entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Manufacturer $manufacturer)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manufacturer_delete', array('id' => $manufacturer->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
