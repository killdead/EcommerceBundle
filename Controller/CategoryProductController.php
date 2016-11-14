<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Ziiweb\EcommerceBundle\Entity\CategoryProduct;
use Ziiweb\EcommerceBundle\Form\CategoryProductType;

/**
 * CategoryProduct controller.
 *
 * @Route("/categoryproduct")
 */
class CategoryProductController extends Controller
{
    /**
     * Lists all CategoryProduct entities.
     *
     * @Route("/", name="categoryproduct_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $categoryProducts = $em->getRepository('ZiiwebEcommerceBundle:CategoryProduct')->findAll();

        return $this->render('categoryproduct/index.html.twig', array(
            'categoryProducts' => $categoryProducts,
        ));
    }

    /**
     * Creates a new CategoryProduct entity.
     *
     * @Route("/new", name="categoryproduct_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $categoryProduct = new CategoryProduct();

        $repository = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct');
        $categories = $repository->findAll();

        $form = $this->createForm('Ziiweb\EcommerceBundle\Form\CategoryProductType', $categoryProduct, array('categories' => $categories));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($categoryProduct);
            $em->flush();

            return $this->redirectToRoute('categoryproduct_edit', array(
                'id' => $categoryProduct->getId(),
                'edit_form' => $form
            ));
        }

       return $this->render('ZiiwebEcommerceBundle:CategoryProduct:new.html.twig', array(
            'categoryProduct' => $categoryProduct,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing CategoryProduct entity.
     *
     * @Route("/{id}/edit", name="categoryproduct_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, CategoryProduct $categoryProduct)
    {
        $repository = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct');
        $categories = $repository->findAll();

        $editForm = $this->createForm('Ziiweb\EcommerceBundle\Form\CategoryProductType', $categoryProduct, array('categories' => $categories));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($categoryProduct);
            $em->flush();

            return $this->render('ZiiwebEcommerceBundle:CategoryProduct:edit.html.twig', array(
                'id' => $categoryProduct->getId(),
                'edit_form' => $editForm->createView()
            ));
        }

        return $this->render('ZiiwebEcommerceBundle:CategoryProduct:edit.html.twig', array(
            'categoryProduct' => $categoryProduct,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a CategoryProduct entity.
     *
     * @Route("/delete/{id}", name="categoryproduct_delete")
     */
    public function deleteAction(Request $request, CategoryProduct $categoryProduct)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($categoryProduct);
        $em->flush();

        if ($request->headers->get('referer') == 'ziiweb_admin_list') {
            $url = $request->headers->get('referer');
        } else {
            $url = $this->generateUrl('ziiweb_admin_default_list', array('entity' => 'CategoryProduct'));
        }

        return new RedirectResponse($url); 
    }

}
