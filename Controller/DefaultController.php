<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ZiiwebEcommerceBundle:Default:index.html.twig');
    }
}
