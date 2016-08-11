<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Ziiweb\EcommerceBundle\Entity\Product;
use Ziiweb\EcommerceBundle\Entity\ProductVersion;
use Ziiweb\EcommerceBundle\Entity\ProductVersionImage;
use Ziiweb\EcommerceBundle\Entity\ProductVersionSize;
use Ziiweb\EcommerceBundle\Form\ProductType;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Product controller.
 *
 * @Route("/product")
 */
class ProductController extends Controller
{
    /**
     * Lists all Product entities.
     *
     * @Route("/", name="product_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $products = $em->getRepository('ZiiwebEcommerceBundle:Product')->findAll();

        return $this->render('product/index.html.twig', array(
            'products' => $products,
        ));
    }

    /**
     * Creates a new Product entity.
     *
     * @Route("/newer", name="product_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $product = new Product();

        if ($request->getMethod() == 'GET') {
            $productVersionImage = new ProductVersionImage();
            $productVersionSize = new ProductVersionSize();
            $productVersion = new ProductVersion();

            $productVersion->addProductVersionImage($productVersionImage);
            $productVersion->addProductVersionSize($productVersionSize);

	    $product->addProductVersion($productVersion);

        } else {
            //I CAN COMMENT THESE LINES BECAUSE I HAVE ADDED 'by_reference => false' TO ProductType > ProductVersions
            /*
            foreach ($requestParameters['product']['productVersions'] as $key => $value) {
		${'productVersion' . $key} = new ProductVersion();
		$product->addProductVersion(${'productVersion' . $key});
            }
            */
        }

        $form = $this->createForm('Ziiweb\EcommerceBundle\Form\ProductType', $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_edit', array(
		'id' => $product->getId()
            ));
        }

        return $this->render('ZiiwebEcommerceBundle:Product:new.html.twig', array(
            'product' => $product,
            'form' => $form->createView()
        ));
    }

    /**
     * Add a new Product to a CategoryProduct.
     *
     * @Route("/add-product", name="product_add_child")
     * @Method({"GET", "POST"})
     */
    public function addChildAction(Request $request)
    {
	$categoryProductId = $request->query->get('parent_id');
	$repository = $this->getDoctrine()->getManager()->getRepository('DefaultBundle:Category');
	$categoryProduct = $repository->findOneBy(array('id' => $categoryProductId));

        $product = new Product();
        $product->setCategoryProduct($categoryProduct);

        if ($request->getMethod() == 'GET') {
            $productVersionImage = new ProductVersionImage();
            $productVersionSize = new ProductVersionSize();
            $productVersion = new ProductVersion();

            $productVersion->addProductVersionImage($productVersionImage);
            $productVersion->addProductVersionSize($productVersionSize);

	    $product->addProductVersion($productVersion);

        } else {
            //I CAN COMMENT THESE LINES BECAUSE I HAVE ADDED 'by_reference => false' TO ProductType > ProductVersions
            /*
            foreach ($requestParameters['product']['productVersions'] as $key => $value) {
		${'productVersion' . $key} = new ProductVersion();
		$product->addProductVersion(${'productVersion' . $key});
            }
            */
        }

        $form = $this->createForm('Ziiweb\EcommerceBundle\Form\ProductType', $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_edit', array(
		'id' => $product->getId()
            ));
        }

        return $this->render('ZiiwebEcommerceBundle:Product:new.html.twig', array(
            'product' => $product,
            'form' => $form->createView()
        ));
    }

    /**
     * Finds and displays a Product entity.
     *
     * @Route("/{id}", name="product_show")
     * @Method("GET")
     */
    public function showAction(Product $product)
    {
        $deleteForm = $this->createDeleteForm($product);

        return $this->render('product/show.html.twig', array(
            'product' => $product,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Product entity.
     *
     * @Route("/{id}/edit", name="product_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Product $product)
    {
        $deleteForm = $this->createDeleteForm($product);

        $editForm = $this->createForm('Ziiweb\EcommerceBundle\Form\ProductType', $product);

	$originalProductVersionSize = new ArrayCollection();
	$originalProductVersionImage = new ArrayCollection();

	// Create an ArrayCollection of the current Tag objects in the database
	foreach ($product->getProductVersions() as $productVersion) {
            foreach ($productVersion->getProductVersionSizes() as $productVersionSize) {
                $originalProductVersionSize->add($productVersionSize);
            }
            foreach ($productVersion->getProductVersionImages() as $productVersionImage) {
                $originalProductVersionImage->add($productVersionImage);
            }
	}

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

	    $em = $this->getDoctrine()->getManager();

	    foreach ($product->getProductVersions() as $productVersion) {
		foreach ($originalProductVersionSize as $productVersionSize) {
		    if (false === $productVersion->getProductVersionSizes()->contains($productVersionSize) ) {
		      $em->remove($productVersionSize);
		    }
		}
		foreach ($originalProductVersionImage as $productVersionImage) {
		    if (false === $productVersion->getProductVersionSizes()->contains($productVersionImage) ) {
		      $em->remove($productVersionImage);
		    }
		}
	    }

            $em->persist($product);
            $em->flush();

            $editForm = $this->createForm('Ziiweb\EcommerceBundle\Form\ProductType', $product);

            return $this->render('ZiiwebEcommerceBundle:Product:edit.html.twig', array(
                'id' => $product->getId(),
                'edit_form' => $editForm->createView()
            ));
        }

        return $this->render('ZiiwebEcommerceBundle:Product:edit.html.twig', array(
            'product' => $product,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Product entity.
     *
     * @Route("/{id}", name="product_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Product $product)
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * Creates a form to delete a Product entity.
     *
     * @param Product $product The Product entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Product $product)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('product_delete', array('id' => $product->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
