<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Ziiweb\EcommerceBundle\Entity\ProductVersion;
use Ziiweb\EcommerceBundle\Form\ProductVersionType;

/**
 * ProductVersion controller.
 *
 * @Route("/productversion")
 */
class ProductVersionController extends Controller
{
    /**
     * Lists all ProductVersion entities.
     *
     * @Route("/", name="productversion_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $productVersions = $em->getRepository('ZiiwebEcommerceBundle:ProductVersion')->findAll();

        return $this->render('productversion/index.html.twig', array(
            'productVersions' => $productVersions,
        ));
    }

    /**
     * Creates a new ProductVersion entity.
     *
     * @Route("/new", name="productversion_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $productVersion = new ProductVersion();
        $form = $this->createForm('Ziiweb\EcommerceBundle\Form\ProductVersionType', $productVersion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($productVersion);
            $em->flush();

            return $this->redirectToRoute('productversion_show', array('id' => $productVersion->getId()));
        }

        return $this->render('productversion/new.html.twig', array(
            'productVersion' => $productVersion,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ProductVersion entity.
     *
     * @Route("/{id}", name="productversion_show")
     * @Method("GET")
     */
    public function showAction(ProductVersion $productVersion)
    {
        $deleteForm = $this->createDeleteForm($productVersion);

        return $this->render('productversion/show.html.twig', array(
            'productVersion' => $productVersion,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ProductVersion entity.
     *
     * @Route("/{id}/edit", name="productversion_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ProductVersion $productVersion)
    {
        $deleteForm = $this->createDeleteForm($productVersion);
        $editForm = $this->createForm('Ziiweb\EcommerceBundle\Form\ProductVersionType', $productVersion);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($productVersion);
            $em->flush();

            return $this->redirectToRoute('productversion_edit', array('id' => $productVersion->getId()));
        }

        return $this->render('productversion/edit.html.twig', array(
            'productVersion' => $productVersion,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ProductVersion entity.
     *
     * @Route("/delete/{id}", name="productversion_delete")
     */
    public function deleteAction(Request $request, ProductVersion $productVersion)
    {
        $form = $this->createDeleteForm($productVersion);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $em->remove($productVersion);
        $em->flush();

        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer); 
    }

    /**
     * Creates a form to delete a ProductVersion entity.
     *
     * @param ProductVersion $productVersion The ProductVersion entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ProductVersion $productVersion)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('productversion_delete', array('id' => $productVersion->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
