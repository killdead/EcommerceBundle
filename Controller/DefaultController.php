<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {


        return $this->render('ZiiwebEcommerceBundle:Default:index.html.twig');
    }

    public function navbarAction()
    {

	$repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct');
	$navbar = $repo->childrenHierarchy();

        return $this->render('ZiiwebEcommerceBundle:Default:navbar.html.twig', array('navbar' => $navbar));
    }
}
