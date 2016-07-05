<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
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
        $form = $this->createForm('Ziiweb\EcommerceBundle\Form\CategoryProductType', $categoryProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($categoryProduct);
            $em->flush();

            return $this->render('ZiiwebEcommerceBundle:CategoryProduct:edit.html.twig', array('id' => $categoryProduct->getId()));
        }

       return $this->render('ZiiwebEcommerceBundle:CategoryProduct:new.html.twig', array(
            'categoryProduct' => $categoryProduct,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a CategoryProduct entity.
     *
     * @Route("/{id}", name="categoryproduct_show")
     * @Method("GET")
     */
    public function showAction(CategoryProduct $categoryProduct)
    {
        $deleteForm = $this->createDeleteForm($categoryProduct);

        return $this->render('categoryproduct/show.html.twig', array(
            'categoryProduct' => $categoryProduct,
            'delete_form' => $deleteForm->createView(),
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
        $deleteForm = $this->createDeleteForm($categoryProduct);
        $editForm = $this->createForm('Ziiweb\EcommerceBundle\Form\CategoryProductType', $categoryProduct);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($categoryProduct);
            $em->flush();

            return $this->redirectToRoute('categoryproduct_edit', array('id' => $categoryProduct->getId()));
        }

        return $this->render('categoryproduct/edit.html.twig', array(
            'categoryProduct' => $categoryProduct,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a CategoryProduct entity.
     *
     * @Route("/{id}", name="categoryproduct_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, CategoryProduct $categoryProduct)
    {
        $form = $this->createDeleteForm($categoryProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($categoryProduct);
            $em->flush();
        }

        return $this->redirectToRoute('categoryproduct_index');
    }

    /**
     * Creates a form to delete a CategoryProduct entity.
     *
     * @param CategoryProduct $categoryProduct The CategoryProduct entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(CategoryProduct $categoryProduct)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('categoryproduct_delete', array('id' => $categoryProduct->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
