<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Ziiweb\EcommerceBundle\Entity\ProductVersionSize;
use Ziiweb\EcommerceBundle\Form\ProductVersionType;

/**
 * ProductVersionSize controller.
 *
 * @Route("/productversionsize")
 */
class ProductVersionSizeController extends Controller
{

    /**
     * Deletes a ProductVersionSize entity.
     *
     * @Route("/delete/{id}", name="productversionsize_delete")
     */
    public function deleteAction(Request $request, ProductVersionSize $productVersionSize)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($productVersionSize);
        $em->flush();

        //DELETE THE REFERER ROUTE TO...
	$ref = str_replace("app_dev.php/", "", parse_url($request->headers->get('referer'),PHP_URL_PATH ));
	$route = $this->container->get('router')->match($ref)['_route'];

        //... DELETE FROM THE LIST ..
        if ($route == 'ziiweb_admin_default_list') {
            $url = $request->headers->get('referer');
        //.. OR DELETE FROM EDIT FORM 
        } else {
            $url = $this->generateUrl('ziiweb_admin_default_list', array('entity' => 'ProductVersionSize'));
        }

        return new RedirectResponse($url); 
    }

}
