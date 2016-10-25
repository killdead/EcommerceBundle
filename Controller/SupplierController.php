<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Ziiweb\EcommerceBundle\Entity\Supplier;
use Ziiweb\EcommerceBundle\Form\SupplierType;

/**
 * Supplier controller.
 *
 * @Route("/supplier")
 */
class SupplierController extends Controller
{
    /**
     * Lists all Supplier entities.
     *
     * @Route("/", name="ziiweb_ecommerce_supplier_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $suppliers = $em->getRepository('ZiiwebEcommerceBundle:Supplier')->findAll();

        return $this->render('supplier/index.html.twig', array(
            'suppliers' => $suppliers,
        ));
    }

    /**
     * Creates a new Supplier entity.
     *
     * @Route("/new", name="ziiweb_ecommerce_supplier_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $supplier = new Supplier();
        $form = $this->createForm('Ziiweb\EcommerceBundle\Form\SupplierType', $supplier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($supplier);
            $em->flush();

            return $this->redirectToRoute('ziiweb_ecommerce_supplier_edit', array(
                'id' => $supplier->getId(),
                'edit_form' => $form->createView()
            ));
        }

        return $this->render('ZiiwebEcommerceBundle:Supplier:new.html.twig', array(
            'supplier' => $supplier,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Supplier entity.
     *
     * @Route("/{id}/edit", name="ziiweb_ecommerce_supplier_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Supplier $supplier)
    {
        $editForm = $this->createForm('Ziiweb\EcommerceBundle\Form\SupplierType', $supplier);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($supplier);
            $em->flush();

            return $this->render('ZiiwebEcommerceBundle:Manufacturer:edit.html.twig', array(
                'id' => $supplier->getId(),
                'edit_form' => $editForm->createView(),
            ));
        }

        return $this->render('ZiiwebEcommerceBundle:Supplier:edit.html.twig', array(
            'supplier' => $supplier,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a Supplier entity.
     *
     * @Route("/delete/{id}", name="ziiweb_ecommerce_supplier_delete")
     */
    public function deleteAction(Request $request, Supplier $supplier)
    {
	$em = $this->getDoctrine()->getManager();
	$em->remove($supplier);
	$em->flush();

        //DELETE THE REFERER ROUTE TO...
	$ref = str_replace("app_dev.php/", "", parse_url($request->headers->get('referer'),PHP_URL_PATH ));
	$route = $this->container->get('router')->match($ref)['_route'];

        //... DELETE FROM THE LIST ..
        if ($route == 'ziiweb_admin_default_list') {
            $url = $request->headers->get('referer');
        //.. OR DELETE FROM EDIT FORM 
        } else {
            $url = $this->generateUrl('ziiweb_admin_default_list', array('entity' => 'Supplier'));
        }

        return new RedirectResponse($url); 
    }

}
