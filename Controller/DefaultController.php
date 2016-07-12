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
        $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();

        return $this->render('ZiiwebEcommerceBundle:Default:index.html.twig', array(
            'csrf_token' => $csrfToken
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

        $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();

	$repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct');
        $categoryProduct = $repo->findOneBy(array('slug' => $categoryProduct));

	$repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersion');

        //$products = $repo->findBy(array('categoryProduct' => $categoryProduct->getId()));


        $qb = $repo->createQueryBuilder('pv')
          ->join('pv.product', 'p')
          ->where('p.categoryProduct = :categoryProduct')
          ->setParameter('categoryProduct', $categoryProduct->getId());

        $query = $qb->getQuery();
        $productVersions = $query->getResult();

        $session = $this->get('session');
        $pedido = null;
        if ($session->has('pedido')) {
          $pedido = $session->get('pedido'); 
        } 

        return $this->render('ZiiwebEcommerceBundle:Default:product_list.html.twig', array(
            'product_versions' => $productVersions,
            'csrf_token' => $csrfToken,
            'pedido' => $pedido
        ));
    }    
    
}
