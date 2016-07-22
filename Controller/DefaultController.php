<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="index") 
     */
    public function indexAction()
    {
        $session = $this->get('session');
        $pedido = null;
        if ($session->has('pedido')) {
          $pedido = $session->get('pedido'); 
        } 

        return $this->render('ZiiwebEcommerceBundle:Default:index.html.twig', array(
            'pedido' => $pedido
        ));
    }

    public function navbarAction()
    {

	$repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct');
	$navbar = $repo->childrenHierarchy();

        return $this->render('ZiiwebEcommerceBundle:Default:navbar.html.twig', array('navbar' => $navbar));
    }

    /**
     * @Route("/categoria/{categoryProduct}", name="product_list") 
     */
    public function listProductsAction($categoryProduct) {

	$repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct');
        $categoryProduct = $repo->findOneBy(array('slug' => $categoryProduct));

	$repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersion');

        $qb = $repo->createQueryBuilder('pv')
          ->join('pv.product', 'p')
          ->join('pv.productVersionSizes', 'pvs')
          ->where('p.categoryProduct = :category_product')
          ->andWhere('pvs.stock > 0')
          ->setParameter('category_product', $categoryProduct->getId());


        $query = $qb->getQuery();
        $productVersions = $query->getResult();

        $session = $this->get('session');
        $pedido = null;
        if ($session->has('pedido')) {
          $pedido = $session->get('pedido'); 
        } 

        return $this->render('ZiiwebEcommerceBundle:Default:product_list.html.twig', array(
            'product_versions' => $productVersions,
            'pedido' => $pedido
        ));
    }    

    /**
     * @Route("/producto/{product_slug}/{product_version_slug}", name="product_show", defaults={"product_version_slug" = null}) 
     */
    public function productShowAction($product_slug, $product_version_slug) {

	$repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersion');

        $qb = $repo->createQueryBuilder('pv')
            ->join('pv.product', 'p')
            ->where('p.slug = :product_slug')
            ->setParameter('product_slug', $product_slug)
        ;

        if ($product_version_slug !== NULL) {
	    $qb->andWhere('pv.slug = :product_version_slug') 
            ->setParameter('product_version_slug', $product_version_slug);
        }
  
        $query = $qb->getQuery();
        $productVersion = $query->getSingleResult();

        $generalStock = 0;
        foreach ($productVersion->getProductVersionSizes() as $productVersionSize) {
            $generalStock += $productVersionSize->getStock();
        }

        $session = $this->get('session');
        $pedido = null;
        if ($session->has('pedido')) {
          $pedido = $session->get('pedido'); 
        } 

        return $this->render('ZiiwebEcommerceBundle:Default:product_show.html.twig', array(
            'product_version' => $productVersion,
            'pedido' => $pedido,
            'general_stock' => $generalStock
        ));
    }
}






