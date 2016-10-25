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
     * Creates a form to delete a ProductVersion entity.
     *
     * @Route("/clar", name="clar")
     * @Method({"GET"})
     */
    public function clarAction(ProductVersion $productVersion)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('productversion_delete', array('id' => $productVersion->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
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
     * Displays a form to edit an existing ProductVersion entity.
     *
     * @Route("/{id}/edit", name="productversion_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ProductVersion $productVersion)
    {
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
        ));
    }

    /**
     * Deletes a ProductVersion entity.
     *
     * @Route("/delete/{id}", name="productversion_delete")
     */
    public function deleteAction(Request $request, ProductVersion $productVersion)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($productVersion);
        $em->flush();

        //DELETE THE REFERER ROUTE TO...
	$ref = str_replace("app_dev.php/", "", parse_url($request->headers->get('referer'),PHP_URL_PATH ));
	$route = $this->container->get('router')->match($ref)['_route'];

        //... DELETE FROM THE LIST ..
        if ($route == 'ziiweb_admin_default_list') {
            $url = $request->headers->get('referer');
        //.. OR DELETE FROM EDIT FORM 
        } else {
            $url = $this->generateUrl('ziiweb_admin_default_list', array('entity' => 'ProductVersion'));
        }

        return new RedirectResponse($url); 
    }

}
