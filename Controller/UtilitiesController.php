<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Ziiweb\EcommerceBundle\Entity\CategoryProduct;
use Ziiweb\EcommerceBundle\Form\CategoryProductType;
use Ziiweb\EcommerceBundle\Entity\TaxRates;

/**
 * Utilities controller.
 */
class UtilitiesController extends Controller
{
    /**
     * @Route("calculate-prices-plus-taxes", name="calculate_prices_plus_taxes") 
     */
    public function calculatePricesPlusTaxes() { 
	$repository = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersion');

	$productVersions = $repository->findAll();

	$em = $this->getDoctrine()->getManager();

	foreach ($productVersions as $productVersion) {
	   $price = $productVersion->getPrice();
	   $productVersion->setPricePlusTaxes(round($price * (1 + TaxRates::VAT_RATE)));
	   
	   $em->merge($productVersion);
	   $em->flush();
	}
     
        return new Response('finished');
    }
}
