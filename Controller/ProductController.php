<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * Displays a form to edit an existing Product entity.
     *
     * @Route("/{id}/edit", name="product_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Product $product)
    {

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
        ));
    }

    /**
     * Deletes a Product entity.
     *
     * @Route("/delete/{id}", name="product_delete")
     */
    public function deleteAction(Request $request, Product $product)
    {
	$em = $this->getDoctrine()->getManager();
	$em->remove($product);
	$em->flush();

        //DELETE THE REFERER ROUTE TO...
	$ref = str_replace("app_dev.php/", "", parse_url($request->headers->get('referer'),PHP_URL_PATH ));
	$route = $this->container->get('router')->match($ref)['_route'];

        //... DELETE FROM THE LIST ..
        if ($route == 'ziiweb_admin_default_list') {
            $url = $request->headers->get('referer');
        //.. OR DELETE FROM EDIT FORM 
        } else {
            $url = $this->generateUrl('ziiweb_admin_default_list', array('entity' => 'Product'));
        }

        return new RedirectResponse($url); 
    }

    /**
     * @Route("/product-autocomplete", name="ziiweb_ecommerce_product_autocomplete") 
     */
    public function autocompleteAction(Request $request)
    {
        $keyword = $request->query->get('term'); 

        //RETRIEVE THE PRODUCTS
        $repository = $this->getDoctrine()->getManager()->getRepository('ZiiwebEcommerceBundle:ProductVersionSize');
        $qb = $repository->createQueryBuilder('pvs')
            ->select("pv.id, pv.price AS price, CONCAT(m.name, ' - ', p.name, ' ', pv.color, ' ', pvs.size) AS label")
            ->join('pvs.productVersion', 'pv')
            ->join('pv.product', 'p')
            ->join('p.manufacturer', 'm')
            ->having('label LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%');

        $result = $qb->getQuery()->getResult();
         
        $json = json_encode($result);


        return new Response($json);
    }
}
