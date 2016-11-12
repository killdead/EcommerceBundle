<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Ziiweb\EcommerceBundle\Entity\TaxRates;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use DefaultBundle\Form\RegistrationType;
use Ziiweb\EcommerceBundle\Form\ShippingDataType;
use Sermepa\Tpv\Tpv;
use Ziiweb\EcommerceBundle\Entity\Purchase;
use Ziiweb\EcommerceBundle\Entity\ProductVersionSizePurchase;

class OrderController extends Controller
{
    /**
     * @Route("/clear-session", name="clear_session")
     */
    public function clearSessionAction()
    {
      $session = $this->get("session");
      $session->clear();

      return new Response("sesion borrada");
    }

    public function calcularTotales2($pedido)
    {
      $pedido['subtotal'] = 0;
      $pedido['iva'] = 0;
      $pedido['re'] = 0;
      $pedido['contrareembolso'] = 0;

      //1.1 calculamos el precio de los productos
      foreach($pedido['subitems'] as $key => $subitem)
      {
	$pedido['subtotal'] += $subitem['precio_total_subitem']; 

	//1.2 calculamos el iva de los productos
	$pedido['iva'] += ($subitem['precio_total_subitem'] * ($pedido['tasa_iva'] - 1)); 

      }

      $aux = $pedido['subtotal'] + $pedido['iva'] + $pedido['re'];

      $pedido['metodo_envio'] = $pedido['metodo_envio_backup'];

      $repositoryMetodoEnvio = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ShippingMethod');
      $metodoEnvio = $repositoryMetodoEnvio->find($pedido['metodo_envio']);
      $gastosEnvio = $metodoEnvio->getPrecio();

      //2.1 sumamos los gastos de envío
      $subtotalBackup = $pedido['subtotal'];
      $pedido['subtotal'] = $pedido['subtotal'] + $gastosEnvio;

      //2.2 calculamos el iva de los gastos de envío
      $ivaBackup = $pedido['iva'];
      $pedido['iva'] += ($gastosEnvio * ($pedido['tasa_iva'] - 1));

      //MÉTODO DE PAGO 
      $repositoryMetodoPago = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:PaymentMethod');
      $metodoPago = $repositoryMetodoPago->find($pedido['metodo_pago']);

      $totalSinMetodoPago = $pedido['subtotal'] + $pedido['iva'] + $pedido['re'];

      if ((int)$pedido['metodo_pago'] == 2) {
	if ($totalSinMetodoPago < 98) {
	  $pedido['contrareembolso'] = $metodoPago->getPrecio(); 
	} else {
	  $pedido['contrareembolso'] = $totalSinMetodoPago * (($metodoPago->getPorcentaje())/100);
	}
	if ($pedido['tasa_iva'] != 1) {
  /*
	  if ($pedido['tasa_re'] == 1) {
  */
	    $pedido['contrareembolso'] = $pedido['contrareembolso'] * $pedido['tasa_iva'];
  /*
	  } else {
	    $pedido['contrareembolso'] = $pedido['contrareembolso'] * ($pedido['tasa_iva'] + ($pedido['tasa_re'] - 1));
	  }
  */
	}
      }

      $pedido['total'] = $pedido['subtotal'] + $pedido['iva'] + $pedido['re'] + $pedido['contrareembolso'];

      if ($pedido['subtotal'] >= 250) {
	$pedido['metodo_envio'] = 3;
	$pedido['subtotal'] = $subtotalBackup;
	$pedido['iva'] = $ivaBackup;
	//$pedido['re'] = $reBackup;
	$pedido['total'] = $subtotalBackup + $ivaBackup + $pedido['re'] + $pedido['contrareembolso'];
      }

      return $pedido;
    }


    /**
     * @Route("/add-subitem", name="add-subitem")
     */
    public function addSubitemAction(Request $request)
    {

      $productVersionSizeId = $request->request->get('product_version_size_id');
      $productoQty = $request->request->get('producto_qty');
      //$size = $request->request->get('size');

      //if check the qty requested is higher than stock, show a message  
      $em = $this->getDoctrine()->getManager();

      $repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersionSize');
      $qb = $repo->createQueryBuilder('pvs')
	  ->where('pvs.id = :product_version_size_id')
	  ->setParameter('product_version_size_id', $productVersionSizeId);

  /*
      if ($size !== null) {
	  $qb->andWhere('pvs.size = :product_version_size')
	  ->setParameter('product_version_size', $size);
      }
  */

      $query = $qb->getQuery();
      $productVersionSize = $query->getSingleResult();

      $stock = $productVersionSize->getStock();
      $newStock = $stock - $productoQty;

      if ($newStock < 0) {
	$response = array('stock' => 'false', 'stock' => $stock);

	$serializer = $this->container->get('jms_serializer');
	$pedidoJson = $serializer->serialize($response, 'json');

	return new JsonResponse($pedidoJson);
      }

      //retrieve session (pedido)
      $user = $this->getUser();
      $session = $this->get('session'); 

      //THERE IS ---NOT--- AN ORDER YET
      if(!$session->has('pedido')) {

	$pedido = array();
	
	///OJOOOOOOOOOOOOOOO si modificamos al go de aqui abajo, tenemos tambien que modificarlo en anadirSubitem()
	if ($this->getUser()) {
	    $pedido['user'] = $this->getUser()->getId();
	}

	$pedido['user_regimen_iva'] = 1;
	$pedido['metodo_envio'] = 3;
	$pedido['metodo_envio_backup'] = 3;
	//asignamos un metodo de pago por defecto: la transferencia.
	$pedido['metodo_pago'] = 1;
	///OJOOOOOOOOOOOOOOO si modificamos algo de aqui arriba, tenemos tambien que modificarlo en anadirSubitem()

	$pedido['tasa_iva'] = TaxRates::VAT_RATE + 1;

	$session->set('pedido', $pedido);
      }
      //THERE IS ---ALREADY--- AN ORDER
      else 
      {
	$pedido = $session->get('pedido');
      }

      $productVersionIdPlusSize = $productVersionSizeId;
      //>>>> PRODUCTO YA EN CARRO <<<<
      //if (isset($pedido['subitems'][$productVersionIdPlusSize])) 
      //{

  /*
	$maxPedido = $subitemColor->getMaxPedido();

	if ($pedido['subitems'][$productVersionId]['qty'] > $maxPedido) {
		$response = array('maxPedido' => 'false', 'maxPedido' => $maxPedido);

		$serializer = $this->container->get('jms_serializer');
		$pedidoJson = $serializer->serialize($response, 'json');

		return new JsonResponse($pedidoJson);
	}


  */
      //>>>> PRODUCTO NUEVO EN CARRO <<<<
      //} else {
	$enCarro = 'false';

	$color = '';
	if ($productVersionSize->getProductVersion()->getColor() != null) {
	  $color = $productVersionSize->getProductVersion()->getColor();
	}

	$name = $productVersionSize->getProductVersion()->getProduct()->getName() . ' ' . $color;

	if ($productVersionSize->getSize() != null) {
	  $size = $productVersionSize->getSize();
	} else {
	  $size =  null;
	}
      
	if (!isset($pedido['subitems'])) {
	    $pedido['subitems'] = array();
	}
	//$pedido['subitems'][$productVersionIdPlusSize]['qty'] = $pedido['subitems'][$productVersionIdPlusSize]['qty'] + $productoQty; 
	//$pedido['subitems'][$productVersionIdPlusSize]['precio_total_subitem'] = $pedido['subitems'][$productVersionIdPlusSize]['precio'] * $pedido['subitems'][$productVersionIdPlusSize]['qty'];
	//$enCarro = 'true';

	$imagePath = $productVersionSize->getProductVersion()->getProductVersionImages()[0]->getFile();

	$aux = array(
	  'id' => $productVersionSizeId, 
	  'qty' => $productoQty, 
	  'precio' => $productVersionSize->getProductVersion()->getPrice(), 
	  'nombre' => $name,
	  'size' => $size,
	  'precio_total_subitem' => ($productVersionSize->getProductVersion()->getPrice() * $productoQty),
	  'image_path' => $imagePath,
	  'qty' => $productoQty
	);

	$pedido['subitems'][$productVersionIdPlusSize] = array_merge($aux, $pedido['subitems']);
      //}

      $pedido = $this->calcularTotales2($pedido);

      $session->set('pedido', $pedido);

      if ($productVersionSize->getProductVersion()->getColor() != null) {
	$colorName = $productVersionSize->getProductVersion()->getColor();
      } else {
	$colorName =  null;
      }
     
      //total number of products
      $totalNumberProducts = 0;
      foreach ($pedido['subitems'] as $subitem) {
	$totalNumberProducts += intval($subitem['qty']);
      }

      $response = array(
	'subtotal' => $pedido['subtotal'],
	'iva' => $pedido['iva'],
	're' => $pedido['re'],
	'total' => $pedido['total'],
	'productoQty' => $pedido['subitems'][$productVersionIdPlusSize]['qty'],
	'nombre' => $pedido['subitems'][$productVersionIdPlusSize]['nombre'],
	'color_name' => $colorName,
	'size' => $productVersionSize->getSize(),
	'precio' => $pedido['subitems'][$productVersionIdPlusSize]['precio'],
	'precio_plus_taxes' => $pedido['subitems'][$productVersionIdPlusSize]['precio'],
	'metodo_envio' => $pedido['metodo_envio'],
	'metodo_pago' => $pedido['metodo_pago'],
	'en_carro' => $enCarro,
	'tasa_iva' => $pedido['tasa_iva'],
	//'tasa_re' => $pedido['tasa_re'],
	'contrareembolso' => $pedido['contrareembolso'],
	'image_path' => $imagePath,
	'total_number_products' => $totalNumberProducts
      );

      //tras la actulización de la variable de session, devolvemos el pedido serializado
      $serializer = $this->container->get('jms_serializer');

      //PERSISTIMOS EL PRODUCTO PARA ALMACENAR EL LA BASE DE DATOS EL NUEVO STOCK
      $productVersionSize->setStock($newStock);
      $em->persist($productVersionSize);
      $em->flush();

      $response['stock'] = $newStock;

      $pedidoJson = $serializer->serialize($response, 'json');

      return new JsonResponse($pedidoJson);
    }



    /**
     * @Route("/remove-subitem-cart", name="remove-subitem-cart")
     */
    public function removeSubitemCartAction(Request $request)
    {
      $productVersionSizeId = $request->request->get('product_version_size_id');
      $size = $request->request->get('size');

      $session = $this->get('session'); 
      $pedido = $session->get('pedido'); 

      $repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersionSize');
      $qb = $repo->createQueryBuilder('pvs')
	 //->join('pvs.productVersion', 'pv')
	 ->where('pvs.id = :product_version_size_id')
	 ->setParameter(':product_version_size_id', $productVersionSizeId)
      ;
     /* 
      if ($size !== NULL) {
	  $qb->andWhere('pvs.size = :product_version_size')
	  ->setParameter('product_version_size', $size);
      }
  */

      $query = $qb->getQuery();
      $productVersionSize = $query->getSingleResult();

      $productVersionIdPlusSize =  $productVersionSizeId;

      $qtyInCart = $pedido['subitems'][$productVersionIdPlusSize]['qty'];

      unset($pedido['subitems'][$productVersionIdPlusSize]);

      //si hay algun producto en el pedido
      if(!empty($pedido['subitems']))
      {
	$pedido = $this->calcularTotales2($pedido);

      } else {

	$pedido = array();

	///OJOOOOOOOOOOOOOOO si modificamos al go de aqui abajo, tenemos tambien que modificarlo en anadirSubitem()
	$pedido['subitems'] = array();
	if ($this->getUser()) {
	    $pedido['user'] = $this->getUser()->getId();
	}
	//$pedido['user_regimen_iva'] = $this->getUser()->getRegimenIva();
	//$pedido['user_reseller'] = $this->getUser()->getReseller();
	////////////////////////////////////
	$pedido['tasa_iva'] = TaxRates::VAT_RATE + 1;
	$pedido['iva'] = 0;
	//$pedido['tasa_re'] = $this->container->getParameter('re');
	//$pedido['re'] = 0;
	$pedido['contrareembolso'] = 0;
	//asignamos un metodo de envio por defecto, porque si el pedido no llega a los 300€, hay que mostrar uno por huevos!!!
  /*
	if ((preg_match('/^35/', $this->getUser()->getPostalCode()) || preg_match('/^38/', $this->getUser()->getPostalCode()) || preg_match('/^07/', $this->getUser()->getPostalCode())) && strtolower($this->getUser()->getCountry()) == 'españa') {
	    $pedido['metodo_envio'] = 4;
	} else {
  */
	    $pedido['metodo_envio'] = 3;
  /*
	}
  */
	//declaramos un indice para guardar el ultimo metodo de envio, por si el total subiese y bajase de los 300€ durante el preenvio.
  /*
	if ((preg_match('/^35/', $this->getUser()->getPostalCode()) || preg_match('/^38/', $this->getUser()->getPostalCode()) || preg_match('/^07/', $this->getUser()->getPostalCode())) && strtolower($this->getUser()->getCountry()) == 'españa') {
	    $pedido['metodo_envio_backup'] = 4;
	} else {
  */
	    $pedido['metodo_envio_backup'] = 3;
  /*
	}
  */

	//asignamos un metodo de pago por defecto: la transferencia.
	$pedido['metodo_pago'] = 1;
	$pedido['subtotal'] = 0;
	$pedido['total'] = 0;
	///OJOOOOOOOOOOOOOOO si modificamos al go de aqui arriba, tenemos tambien que modificarlo en anadirSubitem()
	//

      }

      //total number of products
      $totalNumberProducts = 0;
      foreach ($pedido['subitems'] as $subitem) {
	$totalNumberProducts += intval($subitem['qty']);
      }

      $response = array(
	'subtotal' => $pedido['subtotal'],
	'iva' => $pedido['iva'], 'tasa_iva' => $pedido['tasa_iva'],
	//'re' => $pedido['re'], 'tasa_re' => $pedido['tasa_re'],
	'total' => $pedido['total'],
	'metodo_envio' => $pedido['metodo_envio'],
	'metodo_pago' => $pedido['metodo_pago'],
	'contrareembolso' => $pedido['contrareembolso'],
	'total_number_products' => $totalNumberProducts
      );

      $session->set('pedido', $pedido); 

      $serializer = $this->container->get('jms_serializer');
      $pedidoJson = $serializer->serialize($response, 'json');

      //PERSISTIMOS EL PRODUCTO PARA ALMACENAR EL LA BASE DE DATOS EL NUEVO STOCK
      $em = $this->getDoctrine()->getManager();
      $stock = $productVersionSize->getStock();
      $productVersionSize->setStock($stock + $qtyInCart);
      $em->persist($productVersionSize);
      $em->flush();

      return new JsonResponse($pedidoJson);
    }


    /**
     * @Route("/update-qty-subitem", name="update-qty-subitem")
     */
    public function updateQtySubitemAction(Request $request)
    {
      //$productVersionId = $request->request->get('product_version_id');

      $productVersionSizeId = $request->request->get('product_version_size_id');
      $newProductoQty = $request->request->get('producto_qty');
      $size = $request->request->get('size');

      //if check the qty requested is higher than stock, show a message  
      $em = $this->getDoctrine()->getManager();

      $repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersionSize');
      $qb = $repo->createQueryBuilder('pvs')
	  ->where('pvs.id = :product_version_size_id')
	  ->setParameter('product_version_size_id', $productVersionSizeId);

      //for when the product is added and increased its quantity in the same page
      if ($size == 'undefined') {
	  $size = '';
      }
     /* 
      if ($size !== '') {
	  $qb->andWhere('pvs.size = :product_version_size')
	  ->setParameter('product_version_size', $size);
      }
  */

      $query = $qb->getQuery();
      $productVersionSize = $query->getSingleResult();

      $session = $this->get('session'); 
      $pedido = $session->get('pedido'); 

      $productVersionIdPlusSize = $productVersionSizeId;

      //get the difference between the last qty and new qty: for example '-1', '1', etc
      $differenceQty = $newProductoQty - $pedido['subitems'][$productVersionIdPlusSize]['qty'] ;
      $newStock = $productVersionSize->getStock() - $differenceQty;

      if ($newStock < 0) {
	$response = array('stock' => 0);

	$serializer = $this->container->get('jms_serializer');
	$pedidoJson = $serializer->serialize($response, 'json');

	return new JsonResponse($pedidoJson);
      }

      $pedido['subitems'][$productVersionIdPlusSize]['qty'] = $newProductoQty;
      $pedido['subitems'][$productVersionIdPlusSize]['precio_total_subitem'] = $pedido['subitems'][$productVersionIdPlusSize]['precio'] * $newProductoQty;

      $pedido = $this->calcularTotales2($pedido);

      $session->set('pedido', $pedido); 

      //total number of products
      $totalNumberProducts = 0;
      foreach ($pedido['subitems'] as $subitem) {
	$totalNumberProducts += intval($subitem['qty']);
      }

      $response = array(
	'precio_total_subitem' => $pedido['subitems'][$productVersionIdPlusSize]['precio_total_subitem'],
	'subtotal' => $pedido['subtotal'],
	'iva' => $pedido['iva'],
	'tasa_iva' => $pedido['tasa_iva'],
	//'re' => $pedido['re'], 'tasa_re' => $pedido['tasa_re'],
	'total' => $pedido['total'],
	'stock_qty' => $newStock,
	'metodo_envio' => $pedido['metodo_envio'],
	'contrareembolso' => $pedido['contrareembolso'],
	'total_number_products' => $totalNumberProducts
      );

      $serializer = $this->container->get('jms_serializer');
      $pedidoJson = $serializer->serialize($response, 'json');

      //PERSISTIMOS EL PRODUCTO PARA ALMACENAR EL LA BASE DE DATOS EL NUEVO STOCK
      $productVersionSize->setStock($newStock);
      $em->persist($productVersionSize);
      $em->flush();

      return new JsonResponse($pedidoJson);
    }


    //este es el formulario del avioncito
    public function pedidoPreenvioDomicilioAction()
    {
      $session = $this->get('session'); 
      $pedido = $session->get('pedido'); 

      return $this->render('ProjectFrontendBundle:Pedido:pedido_preenvio_domicilio.html.twig', array('pedido' => $pedido));

    }


    public function pedidoPreenvioAction()
    {
      $session = $this->get('session'); 
      $pedido = $session->get('pedido'); 

      return $this->render('ProjectFrontendBundle:Pedido:pedido_preenvio.html.twig', array('pedido' => $pedido));
    }

    /**
     * @Route("/update-metodo-envio", name="update-metodo-envio")
     */
    public function updateMetodoEnvioAction(Request $request)
    {
      $metodoEnvioId = $request->request->get('metodo_envio');

      $session = $this->get('session'); 
      $pedido = $session->get('pedido'); 

      $pedido['metodo_envio'] = $metodoEnvioId;
      //guardamos el ultimo metodo de envio por si el total subiese y bajase de los 300€ durante el preenvio.
      $pedido['metodo_envio_backup'] = $pedido['metodo_envio'];

      //si elegimos el envio de 48 horas y está elegida la opción de contrareembolso, hay que poner como
      //activa la opción de "Ingreso/transferencia" para que desaparezca del total la cuantia del contrareembolso.
      if ($pedido['metodo_envio'] == 2) {
	$pedido['metodo_pago'] = 1;
      }

      $pedido = $this->calcularTotales2($pedido);

      $response = array(
	'subtotal' => $pedido['subtotal'], 
	'iva' => $pedido['iva'], 'tasa_iva' => $pedido['tasa_iva'],
	're' => $pedido['re'], 'tasa_re' => $pedido['tasa_re'],
	'total' => $pedido['total'],
	'metodo_envio' => $pedido['metodo_envio'],
	'metodo_pago' => $pedido['metodo_pago'],
	'contrareembolso' => $pedido['contrareembolso']
      );

      $session->set('pedido', $pedido); 

      $serializer = $this->container->get('jms_serializer');
      $pedidoJson = $serializer->serialize($response, 'json');

      return new JsonResponse($pedidoJson);
    }


    /**
     * @Route("/update-metodo-pago", name="update-metodo-pago")
     */
    public function updateMetodoPagoAction(Request $request)
    {
      $metodoPagoId = $request->request->get('metodo_pago');

var_dump($metodoPagoId);
      $session = $this->get('session'); 
      $pedido = $session->get('pedido'); 


      $pedido['metodo_pago'] = $metodoPagoId;

      //calculamos totales - calculamos totales
      $pedido = $this->calcularTotales2($pedido);

      $response = array(
	'subtotal' => $pedido['subtotal'], 
	'iva' => $pedido['iva'], 
	'tasa_iva' => $pedido['tasa_iva'], 
	're' => $pedido['re'], 
	//'tasa_re' => $pedido['tasa_re'], 
	'total' => $pedido['total'],
	'metodo_envio' => $pedido['metodo_envio'],
	'metodo_pago' => $pedido['metodo_pago'],
	'contrareembolso' => $pedido['contrareembolso']
      );

var_dump($response);
      $session->set('pedido', $pedido); 

      $serializer = $this->container->get('jms_serializer');
      $pedidoJson = $serializer->serialize($response, 'json');

      return new JsonResponse($pedidoJson);
    } 


    /**
     *  
     *
     * @Route("/pedido-realizar", name="ziiweb_ecommerce_order_pedido_realizar") 
     */
    public function pedidoRealizarAction(Request $request)
    {  
      $em = $this->getDoctrine()->getManager();

      $session = $this->get('session'); 
      $pedido = $session->get('pedido'); 

      //SET DATA TO PURCHASE OBJECT - SET DATA TO PURCHASE OBJECT -
      //SET DATA TO PURCHASE OBJECT - SET DATA TO PURCHASE OBJECT -
      //SET DATA TO PURCHASE OBJECT - SET DATA TO PURCHASE OBJECT -
      //SET DATA TO PURCHASE OBJECT - SET DATA TO PURCHASE OBJECT -
      $purchase = new Purchase();
      if ($user = $this->getUser()) {
          $purchase->setUser($user);
      }

      //DATE COLLECTED IN: 
      //1. THE REGISTER 
      //2. OR IN THE SHIPPING DATA FORM
      $purchase->setName($pedido['name']);
      $purchase->setAddress($pedido['address']);
      $purchase->setTown($pedido['town']);
      $purchase->setProvince($pedido['province']);
      $purchase->setPostalCode($pedido['postal_code']);
      //$purchase->setPhone1($pedido['phone']);


      $purchase->setSubtotal($pedido['subtotal']);
      $purchase->setIva($pedido['iva']);
      $purchase->setTasaIva($pedido['tasa_iva']);
      //$order->setRe($pedido['re']);
      //$order->setTasaRe($pedido['tasa_re']);
      $purchase->setContrareembolso($pedido['contrareembolso']);
      $purchase->setTotal($pedido['total']);



      $repositoryProductVersionSize = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersionSize');


      //STRING FOR TABLE (RESUME PURCHASE) -  STRING FOR TABLE (RESUME PURCHASE) - 
      //STRING FOR TABLE (RESUME PURCHASE) -  STRING FOR TABLE (RESUME PURCHASE) - 
      //STRING FOR TABLE (RESUME PURCHASE) -  STRING FOR TABLE (RESUME PURCHASE) - 
      $stringPedidos = '<table border="1"><tr><th>CÓDIGO PRODUCTO</th><th>CANTIDAD</th><th>MARCA</th><th>NOMBRE PRODUCTO</th><th>PRECIO UNIDAD (sin IVA)</th><th>PRECIO TOTAL (sin IVA)</th></tr>';

      foreach($pedido['subitems'] as $subitem)
      {
	  $producto = $repositoryProductVersionSize->find($subitem['id']);

	  $productVersionSizePurchase = new ProductVersionSizePurchase();

	  $productVersionSizePurchase->setPurchase($purchase);
	  $productVersionSizePurchase->setProductVersionSize($producto);
	  $productVersionSizePurchase->setNumber($subitem['qty']);
	  $productVersionSizePurchase->setPrecioTotalSubitem($subitem['precio_total_subitem']);

	  $purchase->addProductVersionSizePurchase($productVersionSizePurchase);

	  $colorName = $producto->getProductVersion()->getColor(); 

	  $stringPedidos = $stringPedidos . 
	    '<tr><td>' . $producto->getCode() . '</td>' . //codigo producto
	    '<td align="right">' . $subitem['qty'] . '</td>' . //cantidad
	    '<td>' . $producto->getProductVersion()->getProduct()->getManufacturer()->getName() . '</td>' . //marca
	    '<td>' . $producto->getProductVersion()->getProduct()->getName() . ' ' . $colorName . '</td>' . //nombre
	    '<td align="right">' . number_format($subitem['precio'], 2, ',', '') . ' €' . '</td>' .  //precio unidad (sin iva)
	    '<td align="right">' . number_format(($subitem['precio_total_subitem']), 2, ',', '') . ' €</td></tr><br>'; //precio x cantidad (sin iva)
      }

      $stringPedidos = $stringPedidos . '</table>';

      $repositoryMetodoEnvio = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ShippingMethod');
      $metodoEnvio = $repositoryMetodoEnvio->find($pedido['metodo_envio']);
      $purchase->setMetodoEnvio($metodoEnvio);

      $repositoryMetodoPago = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:PaymentMethod');
      $metodoPago = $repositoryMetodoPago->find($pedido['metodo_pago']);
      $purchase->setMetodoPago($metodoPago);


      $stringPedidos = $stringPedidos .
	'SUBTOTAL: ' . number_format((float)$purchase->getSubtotal() - $purchase->getMetodoEnvio()->getPrecio(), 2, ',', '') . ' €' . '<br>' .
	'GASTOS DE ENVÍO: ' . number_format($purchase->getMetodoEnvio()->getPrecio(), 2, ',', '') . '€' . '<br>' .
	'IVA General (' . (($pedido['tasa_iva'] - 1) * 100) . '%): ' . number_format($pedido['iva'] , 2, ',', '') . ' €' . '<br>';

/*
      if ($pedido['re'] != 0) {
	$stringPedidos = $stringPedidos . 'R.E. General (' . number_format((($pedido['tasa_re'] - 1) * 100), 1, ',', '') . '%): ' . number_format($pedido['re'] , 2, ',', '') . ' €' . '<br>';
      }
*/

      if ($pedido['contrareembolso'] != 0) {
	$stringPedidos = $stringPedidos . 'Contrareembolso: ' . number_format($pedido['contrareembolso'] , 2, ',', '') . ' €' . '<br>';
      }

      $stringPedidos = $stringPedidos . 

      'TOTAL (IVA inc): ' . number_format((float)$purchase->getTotal(), 2, ',', '') . ' €' . '<br>' . '<br>'; 

      $em->persist($purchase);
      $em->flush();

      $message = \Swift_Message::newInstance()
	->setContentType('text/html')
	->setSubject('Pedido realizado en la web de Pro Comunicaciones')
	->setFrom(array('hola@procomunicaciones.es'))
	->setTo(array(
	  //'tirengarfio@gmail.com',
	  //'pedidos@procomunicaciones.es'
	  'info@ziiweb.com'
	))
	->setBody(
	  'Estimado Cliente,<br><br>' . 
	  'Gracias por tu compra en PRO COMUNICACIONES.<br><br>' . 
	  'Más abajo tienes el resumen de tu pedido:<br><br>' .
	  $stringPedidos .
	  $metodoEnvio->getNombre() . '<br>' .
	  $metodoPago->getNombre() . '<br><br>' .
	  'En un plazo máximo de 24 horas hábiles, recibirás la factura proforma a través de tu correo electrónico.<br><br>' .
	  'Para cualquier otra consulta escribenos a <a href="mailto:pedidos@procomunicaciones.es">pedidos@procomunicaciones.es</a><br><br>' .
	  'Gracias por confiar en nosotros.<br><br>' .
	  'Atentamente,<br><br>' .
	  'PRO COMUNICACIONES CB<br>' . 
	  'Tu proveedor de confianza.<br><br>'  
	);

      $this->get('mailer')->send($message);

/*
      $reseller = $user->getReseller() ? 'Sí' : 'No';
      $regimenIva = $user->getRegimenIva();

      if ($regimenIva == 0) {
	$regimenIvaText = 'Régimen general';
      } else if ($regimenIva == 1) {
	$regimenIvaText = 'Intracomunitario';
      } else {
	$regimenIvaText = 'Recargo de equivalencia';
      }
*/

      //CHECK THE NEW ADDRESS (SHIPPING DATA FORM) IS DIFFERENT FROM THE ORIGINAL
      $nuevaDireccion = '';
      if ($this->getUser()) {
	if (
	    //$user->getCompany() != trim($purchase->getCompany()) || 
	    //$user->getShopName() != trim($purchase->getShopName()) || 
	    $user->getAddress() != trim($purchase->getAddress()) || 
	    $user->getTown() != trim($purchase->getTown()) || 
	    $user->getPostalCode() != trim($purchase->getPostalCode()) || 
	    $user->getProvince() != trim($purchase->getProvince()) //|| 
	    //$user->getPhone1() != trim($purchase->getPhone()) || 
	    //$user->getTimetable() != trim($purchase->getTimetable()
	) {
	$nuevaDireccion = 
	    '<strong><span style="color: #D51B1B">IMPORTANTE: el cliente solicita la entrega del pedido en una dirección diferente a la dirección de facturación, prefiere que se le contacte en otro número de teléfono o en otro horario diferente al de su información de registro:</span></strong>' . '<br><br>' . 
	    'Empresa: ' . $purchase->getCompany() . '<br>'.
	    'Nombre comercial (tienda/local): ' . $purchase->getShopName() . '<br>' .
	    'Dirección:' . $purchase->getAddress() . '<br>' .
	    'Localidad:' . $purchase->getTown() . '<br>' .
	    'Código postal: ' . $purchase->getPostalCode() . '<br>' .
	    'Provincia: ' . $purchase->getProvince() . '<br>' .
	    'Teléfono: ' . $purchase->getPhone() . '<br>' .
	    'Horario: ' . $purchase->getTimetable() . '<br><br>'; 
	}

      }

      if ($this->getUser()) {
	  $email = $this->getUser()->getEmail();
      } else {
	  $email = $pedido['email'];
      }

      $emailBody = 
	  //'Código cliente: ' . $user->getClientCode() . '<br>' . 
	  //'Empresa: ' . $user->getCompany() . '<br>' . 
	  //'Nombre comercial (tienda/local): ' . $user->getShopName() . '<br>' . 
	  //'NIF/CIF: ' . $user->getCif() . '<br>' . 
	  'Dirección: ' . $pedido['address'] . '<br>' .
	  'Código postal: ' . $pedido['postal_code'] . '<br>' .
	  'Provincia: ' . $pedido['province'] . '<br>' . 
	  'Email: ' . $email . '<br>' . 
	  'Localidad: ' . $pedido['town'] . '<br>' .
	  'Tfno: ' . $pedido['phone'] . '<br>' .
	  //'Teléfono 1: ' . $user->getPhone1() . '<br>' .
	  //'Teléfono 2: ' . $user->getPhone2() . '<br>' . 
	  //'Régimen IVA: ' . $regimenIvaText . '<br>' .
	  //'¿Tiene la condición de revendedor?: ' . $reseller . '<br><br>' .
	  $nuevaDireccion . 
	  $stringPedidos . '<br>' .
	  $metodoEnvio->getNombre() . '<br>' .
	  $metodoPago->getNombre();

      $message = \Swift_Message::newInstance()
	->setContentType('text/html')
	->setSubject('Nuevo pedido Pro Comunicaciones')
	->setFrom(array('hola@procomunicaciones.es'))
	->setTo(array(
	  'pedidos@procomunicaciones.es'
	  //'tirengarfio@gmail.com'
	))
	->setBody($emailBody);

      $this->get('mailer')->send($message);

      //SAVE "PAYMENT METHOD" ____TO SHOW ONE MESSAGE OR OTHER____ IN THE TEMPLATE DEPENDING ON THE PAYMENT METHOD CHOSEN
      $paymentMethod = $pedido['metodo_pago'];

      $session->remove('pedido');

      return $this->render('ZiiwebEcommerceBundle:Order:pedido_realizar.html.twig', array(
          'payment_method' => $paymentMethod
      ));
    }


    public function pedidoRealizarBackendAction(Request $request)
    {  
      $em = $this->getDoctrine()->getManager();

      //>>>>>>>> BORRAMOS LOS pedidoSubitem EXISTENTES PARA PONER UNOS NUEVOS <<<<<<<<
      $repositoryPedidoSubitem = $this->getDoctrine()->getRepository('ProjectBackendBundle:PedidoSubitem');
      $pedidoSubitems = $repositoryPedidoSubitem->findBy(array('pedido' => $request->query->get('pedido_id'))); 
      foreach($pedidoSubitems as $pedidoSubitem)
      {
	$em->remove($pedidoSubitem);
      }
      $em->flush();
      //>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>><<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

      $session = $this->get('session'); 
      $pedido = $session->get('pedido'); 

      $repositoryPedido = $this->getDoctrine()->getRepository('ProjectBackendBundle:Pedido');
      $order = $repositoryPedido->find($request->query->get('pedido_id'));

      $order->setSubtotal($pedido['subtotal']);
      $order->setIva($pedido['iva']);
      $order->setTotal($pedido['total']);


      $repositorySubitemColor = $this->getDoctrine()->getRepository('ProjectBackendBundle:SubitemColor');
      foreach($pedido['subitems'] as $subitem)
      {
	$pedidoSubitem = new PedidoSubitem();

	$producto = $repositorySubitemColor->find($subitem['id']);

	$pedidoSubitem->setPedido($order);
	$pedidoSubitem->setSubitemColor($producto);
	$pedidoSubitem->setNumber($subitem['qty']);
	$pedidoSubitem->setPrecioTotalSubitem($subitem['precio_total_subitem']);
	$order->addPedidoSubitem($pedidoSubitem);
      }

      $repositoryMetodoEnvio = $this->getDoctrine()->getRepository('ProjectBackendBundle:MetodoEnvio');
      $metodoEnvio = $repositoryMetodoEnvio->find($pedido['metodo_envio']);
      $order->setMetodoEnvio($metodoEnvio);

      $repositoryMetodoPago = $this->getDoctrine()->getRepository('ProjectBackendBundle:MetodoPago');
      $metodoPago = $repositoryMetodoPago->find($pedido['metodo_pago']);
      $order->setMetodoPago($metodoPago);

      $em->persist($order);
      $em->flush();

      $session->remove('pedido');

      return $this->render('ProjectFrontendBundle:Pedido:pedido_realizar.html.twig');
    }


    /**
     * @Route("/pedido-resumen", name="ziiweb_ecommerce_order_preenvio_resumen")
     */
    public function preenvioResumenAction() 
    {
      $repository = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ShippingMethod');
      $shippingMethods = $repository->findBy(array('enabled' => true));

      $repository = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:PaymentMethod');
      $paymentMethods = $repository->findBy(array('enabled' => true));

      $session = $this->get('session'); 
      $pedido = $session->get('pedido'); 
      
      return $this->render('ZiiwebEcommerceBundle:Order:preenvio_resumen.html.twig', array(
	  'shippingMethods' => $shippingMethods,
	  'paymentMethods' => $paymentMethods,
	  'pedido' => $pedido
      ));
    }


    /**
     * @Route("/registro-login", name="registration_login")
     */
    public function registrationLoginAction(Request $request) 
    {
	$registrationForm = $this->createForm(RegistrationType::class);

	$session = $this->get('session'); 
	$pedido = $session->get('pedido'); 

	return $this->render('ZiiwebEcommerceBundle:Order:registration_login.html.twig', array(
	    'registration_form' => $registrationForm->createView(),
	    'pedido' => $pedido
	));
    }

    /**
     * The user is currently logged in: lets give the choice to ___change the shipping data___
     * 
     * GIVE THE CHANCE TO ___CHANGE THE SHIPPING DATA____ (IF THE USER IS CURRENTLY ___LOGGED IN___ DURING THE PURCHASE)
     *
     * @Route("/direccion-envio", name="shipping_data")
     */
    public function shippingDataAction(Request $request) 
    {

	$form = $this->createForm(ShippingDataType::class, $this->getUser());

	$form->handleRequest($request);

	if ($form->isValid()) {
	    $user = $form->getData();
 
	    $em = $this->getDoctrine()->getManager();
	    $em->persist($user); 
	    $em->flush($user); 
	    
	    $session = $this->get('session'); 
	    $pedido = $session->get('pedido'); 

            //ADD TO "SESSION" THE DATA INSERTED IN THE FORM
	    $pedido['name'] = $form->getData()->getName();
	    $pedido['address'] = $form->getData()->getAddress();
	    $pedido['town'] = $form->getData()->getTown();
	    $pedido['postal_code'] = $form->getData()->getPostalCode();
	    $pedido['province'] = $form->getData()->getProvince();
	    $pedido['country'] = $form->getData()->getCountry();
	    $pedido['phone'] = $form->getData()->getPhone1();

            $session->set('pedido', $pedido); 

	    //THE USER WANTS TO PAY WITH ___CREDIT CARD____: REDIRECT TO PAYMENT PLATFORM
	    if ($pedido['metodo_pago'] == '3') {
	        return $this->redirectToRoute('redsys_payment');
	    //OTHER PAYMENTS 
	    } else {
	        return $this->redirectToRoute(
                    'ziiweb_ecommerce_order_pedido_realizar'
              );
	    }
	}

	return $this->render('ZiiwebEcommerceBundle:Order:shipping_data.html.twig', array(
	    'form' => $form->createView()
	));
    }


    public function preenvioEnvioAction() 
    {
      return $this->render('ProjectFrontendBundle:Pedido:preenvio_envio.html.twig');
    }


    public function preenvioPagoAction() 
    {
      return $this->render('ProjectFrontendBundle:Pedido:preenvio_pago.html.twig');
    }

    /**
     * @Route("/noti", name="redsys_noti")
     */
    function redsysNotiAction() {
       return new Response("noti");
    }

    /**
     * @Route("/payment-ok", name="payment_ok")
     */
    function redsysOkAction() {

        return $this->redirect();
        //return $this->render('ZiiwebEcommerceBundle:Order:payment_ok.html.twig');
    }

    /**
     * @Route("/payment-ko", name="payment_ko")
     */
    function redsysKoAction() {
        return $this->render('ZiiwebEcommerceBundle:Order:payment_ko.html.twig');
    }

    /**
     * @Route("/redsys-payment", name="redsys_payment")
     */
    function redsysPaymentAction() {


        $session = $this->get('session');
        $pedido = $session->get('pedido');

	try{
	    //Key test
	    $key = 'sq7HjrUOBfKmC576ILgskD5srU870gJ7';

	    $redsys = new Tpv();
	    $redsys->setAmount($pedido['total']);
	    $redsys->setOrder(time());
	    $redsys->setMerchantcode('092536168'); //Reemplazar por el código que proporciona el banco
	    $redsys->setCurrency('978');
	    $redsys->setTransactiontype('0');
	    $redsys->setTerminal('001');


	    $redsys->setMethod('C'); //Solo pago con tarjeta, no mostramos iupay
	    //$redsys->setNotification('http://localhost/noti.php'); //Url de notificacion
	    $redsys->setNotification('http://my_bundles/app_dev.php/noti'); //Url de notificacion
	    //$redsys->setUrlOk('http://localhost/ok.php'); //Url OK
	    $redsys->setUrlOk($this->generateUrl('ziiweb_ecommerce_order_pedido_realizar', array(), UrlGeneratorInterface::ABSOLUTE_URL)); //Url OK
	    //$redsys->setUrlKo('http://localhost/ko.php'); //Url KO
	    $redsys->setUrlKo($this->generateUrl('payment_ko', array(), UrlGeneratorInterface::ABSOLUTE_URL)); //Url KO
	    $redsys->setVersion('HMAC_SHA256_V1');
	    $redsys->setTradeName('Sugar');
	    $redsys->setTitular('Pedro Risco');
	    $redsys->setProductDescription('Compras varias');


            ////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////
	    $redsys->setEnviroment('test'); //Entorno test
            ////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////

	    $signature = $redsys->generateMerchantSignature($key);
	    $redsys->setMerchantSignature($signature);

	    $redsys->executeRedirection();
	    $form = $redsys->createForm();
	}
	catch(Exception $e){
	    echo $e->getMessage();
	}
    }
}
