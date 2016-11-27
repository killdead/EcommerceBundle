<?php

namespace Ziiweb\EcommerceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ziiweb\EcommerceBundle\Filter\FilterType;
use Ziiweb\EcommerceBundle\Entity\TaxRates;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DefaultController extends Controller
{
    /**
     * @Route("/breadcrumbs", name="ziiweb_ecommerce_default_breadcrumbs") 
     */
    public function breadcrumbsAction($categoryProductSlug)
    {
        $categoryProduct = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct')->findOneBy(array('slug' => $categoryProductSlug));

        $repository = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct');

        $index = 0;
        $parentCategories[$index]['slug'] = $categoryProduct->getSlug();
        $parentCategories[$index]['name'] = $categoryProduct->getName();

        $categoryAux = $categoryProduct;


        while ($categoryAux->getParent() != NULL){
          $index++;

          $categoryAux = $categoryProduct->getParent();

          $aux = array();
          $aux['slug'] = $categoryAux->getSlug();
          $aux['name'] = $categoryAux->getName();
 
          array_unshift($parentCategories, $aux);
        }; 

        return $this->render('ZiiwebEcommerceBundle:Default:breadcrumbs.html.twig', array(
            'parentCategories' => $parentCategories,
        ));
    } 

    /**
     * @Route("/generateFilter/{category_product}", name="ziiweb_ecommerce_default_generate_filter") 
     */
    public function generateFilterAction($category_product)
    {

        //get the id's of the children categories
        $categoryProduct = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct')->findOneBy(array('slug' => $category_product));
        $repository = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct');
        $childrenHierarchy = $repository->childrenHierarchy($categoryProduct, false, array(), true);
	$res = array();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($childrenHierarchy), \RecursiveIteratorIterator::SELF_FIRST);
	foreach ($iterator as $k => $v) {
	    if ($k === 'id') {
		$res[] = $v;
	    }
	}

        $filterColumns = array(
            array(
		'name' => 'price',
                'label' => 'Precio',
		'class' => 'ZiiwebEcommerceBundle:ProductVersion',
		'type' => 'range',
		'currency' => '1',
		'add_taxes' => '1',
		'step' => '0.01'),
            array(
		'name' => 'size',
                'label' => 'Talla',
		'class' => 'ZiiwebEcommerceBundle:ProductVersionSize',
		'type' => 'checkbox'),
            array(
		'name' => 'color',
                'label' => 'Color',
		'class' => 'ZiiwebEcommerceBundle:ProductVersion',
		'type' => 'checkbox')
        );

        $options = array();

	//max an min values of each column
        foreach ($filterColumns as &$column) {
            //RANGE - RANGE - RANGE - RANGE - RANGE - RANGE - RANGE - RANGE - RANGE - 
            if ($column['type'] == 'range') {
		//ProductVersion
		if ($column['class'] == 'ZiiwebEcommerceBundle:ProductVersion') {
		    $repository = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersion');
		    $qb = $repository->createQueryBuilder('pv')
			->select('MAX(pv.' . $column['name']  . ') AS max_value, MIN(pv.' . $column['name'] .') AS min_value')
			->join('pv.product', 'p')
			->join('pv.productVersionSizes', 'pvs')
			//enabled
			->where('pv.enabled = ?1')
			->andWhere('p.categoryProduct IN (:categoryProduct)')
			//stock
			->andWhere('pvs.stock > ?2')
			->setParameter('categoryProduct', $res)
			->setParameter(1, 1)
			->setParameter(2, 0)
		    ;

		    $query = $qb->getQuery();
		    $result = $query->getSingleResult();
     
		    $column['values'] =  array(
                        'min' => $result['min_value'],
                        'max' => $result['max_value']
                    );

		//ProductVersionSize
		} else if ($column['class'] == 'ZiiwebEcommerceBundle:ProductVersionSize') {
		    $repository = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersionSize');
		    $qb = $repository->createQueryBuilder('pvs')
			->select('MAX(pvs.' . $column['name']  . ') AS max_value, MIN(pvs.' . $column['name'] .') AS min_value')
			->join('pvs.productVersion', 'pv')
			->join('pv.product', 'p')
			//enabled
			->where('pv.enabled = ?1')
			->andWhere('p.categoryProduct IN (:categoryProduct)')
                        //->setMaxResults(4)
			//stock
			->andWhere('pvs.stock > ?2')
			->setParameter('categoryProduct', $res)
			->setParameter(1, 1)
			->setParameter(2, 0)
		    ;

		    $query = $qb->getQuery();
		    $result = $query->getSingleResult();
     
		    $column['values'] =  array(
                        'min' => $result['min_value'],
                        'max' => $result['max_value']
                    );
		}
            //CHECKBOX - CHECKBOX - CHECKBOX - CHECKBOX - CHECKBOX - CHECKBOX - CHECKBOX - 
            } else if ($column['type'] == 'checkbox') {
                //ProductVersion
                if ($column['class'] == 'ZiiwebEcommerceBundle:ProductVersion') {

		    $qb = $this->getDoctrine()->getManager()->createQueryBuilder()
			->select('DISTINCT pv.' . $column['name'])
			->from('ZiiwebEcommerceBundle:ProductVersion', 'pv', 'pv.' . $column['name'])
			->join('pv.product', 'p')
			->leftJoin('pv.productVersionSizes', 'pvs')
			//enabled
			->where('pv.enabled = ?1')
			->andWhere('p.categoryProduct IN (:categoryProduct)')
			//stock
			->andWhere('pvs.stock > ?2')
			->orderBy('pv.' . $column['name'], 'ASC')
			->setParameter('categoryProduct', $res)
			->setParameter(1, 1)
			->setParameter(2, 0)
		    ;

		    $query = $qb->getQuery();
		    $result = $query->getResult();

       
		    //construct an array being the key and value the same values
		    foreach ($result as $key => $value) {
			$column['values'][$key] = $key;
		    }

                //ProductVersionSize 
		} else if ($column['class'] == 'ZiiwebEcommerceBundle:ProductVersionSize') {
		    $qb = $this->getDoctrine()->getManager()->createQueryBuilder()
			->select('DISTINCT pvs.' . $column['name'])
                        ->from('ZiiwebEcommerceBundle:ProductVersionSize', 'pvs', 'pvs.' . $column['name'])
			->join('pvs.productVersion', 'pv')
			->join('pv.product', 'p')
			//enabled
			->where('pv.enabled = ?1')
			->andWhere('p.categoryProduct IN (:categoryProduct)')
			//stock
			->andWhere('pvs.stock > ?2')
                        ->orderBy('pvs.' . $column['name'], 'ASC')
			->setParameter('categoryProduct', $res)
			->setParameter(1, 1)
			->setParameter(2, 0)
		    ;

		    $query = $qb->getQuery();
		    $result = $query->getResult();
     
                    //construct an array being the key and value the same values
                    foreach ($result as $key => $value) {
		      $column['values'][$key] = $key;
                    }
                }
            }
        }



        $filter = $this->createForm(FilterType::class, null, array('filter_config' => $filterColumns));

        return $this->render('ZiiwebEcommerceBundle:Default:filter_generator.html.twig', array(
            'filter' => $filter->createView(),
        ));
    }
   
    /**
     * @Route("/filter", name="filter") 
     */
    public function filterAction(Request $request)
    {
        $filter = $request->request->get('filter');
        $categoryProduct = $request->request->get('category_product');

        //get the id's of the children categories
        $categoryProductId = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct')->findOneBy(array('slug' => $categoryProduct));
        $repository = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct');
        $childrenHierarchy = $repository->childrenHierarchy($categoryProductId, false, array(), true);
	$res = array();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($childrenHierarchy), \RecursiveIteratorIterator::SELF_FIRST);
	foreach ($iterator as $k => $v) {
	    if ($k === 'id') {
		$res[] = $v;
	    }
	}

        $filterColumns = array(
            array(
		'name' => 'price',
                'label' => 'Precio',
		'class' => 'ZiiwebEcommerceBundle:ProductVersion',
		'type' => 'range',
		'currency' => '1',
		'add_taxes' => '1',
		'step' => '0.01'),
            array(
		'name' => 'size',
                'label' => 'Talla',
		'class' => 'ZiiwebEcommerceBundle:ProductVersionSize',
		'type' => 'checkbox'),
            array(
		'name' => 'color',
                'label' => 'Color',
		'class' => 'ZiiwebEcommerceBundle:ProductVersion',
		'type' => 'checkbox')
        );

        //query taking into account if we are in the ---WISHLIST---
	$repository = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersion');
        //SEARCH - SEARCH - SEARCH - SEARCH - SEARCH - SEARCH - SEARCH - 
        if ($categoryProduct == 'search') {
	  $qb = $repository->createQueryBuilder('pv')
             ->select("DISTINCT pv, CONCAT(p.name, ' ', m.name, ' ', pv.colorCode) AS full_product_name")
	     ->join('pv.product', 'p')
	     ->join('p.manufacturer', 'm')
	     ->join('pv.productVersionSizes', 'pvs')
	     ->andWhere('pv.enabled = ?1')
	     ->andWhere('pvs.stock > ?2')
             ->having('full_product_name LIKE :name')
             ->setParameter('name', '%'. $filter . '%')
	     ->setParameter(1, 1)
	     ->setParameter(2, 0)
          ;

        //WISHLIST - WISHLIST - WISHLIST - WISHLIST - WISHLIST - WISHLIST 
        } else if ($categoryProduct == 'wishlist') {
	  $qb = $repository->createQueryBuilder('pv')
	     ->join('pv.users', 'u')
             ->where('u.id = :u_id')
             ->setParameter('u_id', $this->getUser()->getId())
          ;
        //FEATURED - FEATURED - FEATURED - FEATURED - FEATURED - 
        } else if ($categoryProduct == 'ziiweb_ecommerce_default_featured_products') {
	  $qb = $repository->createQueryBuilder('pv')
             ->select("DISTINCT pv")
	     ->join('pv.product', 'p')
	     ->join('pv.productVersionSizes', 'pvs')
             ->where('pv.featured = :pv_featured')
	     ->andWhere('pv.enabled = ?1')
	     ->andWhere('pvs.stock > ?2')
             ->setParameter('pv_featured', 1)
	     ->setParameter(1, 1)
	     ->setParameter(2, 0)
          ;
        } else {
	    //retrieve the columns for those where there is a filter value 
	    foreach ($filter['filter'] as $key => $filterValue) {
		$aux = array_values(array_filter($filterColumns, function($value) use ($key) {
			return $value['name'] == $key;  
		    }
		))[0];
		//add the filter values
		$aux['values'] = $filterValue;
		$filterColumnsConfig[] = $aux;
	    }

	    $qb = $repository->createQueryBuilder('pv')
	      ->select('DISTINCT pv')
	      ->join('pv.product', 'p')
	      ->join('p.categoryProduct', 'cp')
	      ->join('pv.productVersionSizes', 'pvs')
	      ->andWhere('cp.id IN (:categoryProduct)')
	      ->andWhere('pv.enabled = ?1')
	      ->andWhere('pvs.stock > ?2')
	      //->setFirstResult(0)
	      ->setParameter('categoryProduct', $res)
	      ->setParameter(1, 1)
	      ->setParameter(2, 0)
	    ;

	    foreach ($filterColumnsConfig as $key => $filterColumnConfig) {
	        if ($filterColumnConfig['class'] == 'ZiiwebEcommerceBundle:ProductVersion') {
		    if ($filterColumnConfig['type'] == 'range') {
		        $qb->andWhere('pv.' . $filterColumnConfig['name'] . ' >= :min AND pv.' . $filterColumnConfig['name'] . ' <= :max');
		        $qb->setParameter('min', $filterColumnConfig['values']['min']);
		        $qb->setParameter('max', $filterColumnConfig['values']['max']);
		    } else if ($filterColumnConfig['type'] == 'checkbox') {
		        $qb->andWhere('pv.' . $filterColumnConfig['name'] . ' IN (:values' . $key . ')');
		        $qb->setParameter('values' . $key, $filterColumnConfig['values']);
		    }
	       } else if ($filterColumnConfig['class'] == 'ZiiwebEcommerceBundle:ProductVersionSize') {
		    if ($filterColumnConfig['type'] == 'range') {
		        $qb->andWhere('pvs.' . $filterColumnConfig['name'] . ' >= :min' . $key . ' AND pvs.' . $filterColumnConfig['name'] . ' <= :max' . $key);
		        $qb->setParameter('min' . $key, $filterColumnConfig['values']['min']);
		        $qb->setParameter('max' . $key, $filterColumnConfig['values']['max']);
		    } else if ($filterColumnConfig['type'] == 'checkbox') {
		        $qb->andWhere('pvs.' . $filterColumnConfig['name'] . ' IN (:values' . $key . ')');
		        $qb->setParameter('values' . $key, $filterColumnConfig['values']);
		   }
	       }
	   }
        } 
     
       $query = $qb->getQuery();
       $totalProductVersions = $query->getResult();

       $maxResults = 8;
       $page = $request->request->get('page') ? : 1;

       $firstResult = $maxResults * ($page - 1);

       $qb
	  ->setMaxResults($maxResults)
	  ->setFirstResult($firstResult)
       ;

       $query = $qb->getQuery();
       $result = $query->getResult();

       $response = new JsonResponse();

       $renderedView = $this->renderView('ZiiwebEcommerceBundle:Default:product_list_inner.html.twig', array('product_versions' => $result));
       $response->setData(array(
           'rendered_view' => $renderedView,
           'count_product_versions' => count($totalProductVersions),
           'max_results' => $maxResults 
       ));

       return $response;
    }

    /**
     * @Route("/", name="ziiweb_ecommerce_default_index") 
     */
    public function indexAction()
    {
	$repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersion');
        $productVersions = $repo->findBy(array('featured' => true));

        return $this->render('ZiiwebEcommerceBundle:Default:index.html.twig', array(
            'product_versions' => $productVersions
        ));
    }

    /**
     * @Route("/navbar/", name="navbar") 
     */
    public function navbarAction(Request $request)
    {
	$repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct');

        //$currentCategoryProduct = $request-get('current_category_product');

	$navbar = $repo->childrenHierarchy();

        //retrieve the categories that has products, are enabled and with stock > 0.
        $query = $repo->createQueryBuilder('cp')         
          ->leftJoin('cp.parent', 'cpi')
          ->join('cp.products', 'p')
          ->join('p.productVersions', 'pv')
          ->join('pv.productVersionSizes', 'pvs')
          ->where('pv.enabled = 1')
          ->where('pvs.stock > 0')
          ->distinct()
          ->getQuery();
         
        $result = $query->getResult();

        //retrieve the ANCESTORS IDs for each category
        $nodeIds = array();
        foreach ($result as $item) {
	    if ($item->getParent() != null) {
		$nodeIds[] = $item->getId(); 
		do {
		    $item = $item->getParent();
		    $nodeIds[] = $item->getId();
		} while ($item->getParent() != null) ;
	    } else {
		$nodeIds[] = $item->getId();
	    }
        }

        //__plain array__ (without hierarchies)
	$nodesHierarchy = $repo->getNodesHierarchy();

        foreach ($nodesHierarchy as $key => $node) { 
          if (!in_array($node['id'], $nodeIds)) { 
            unset($nodesHierarchy[$key]);
          }
        }

        //construct the TREE using the categories that has products, are enabled and with stock > 0 and their ANCESTORS
	$options = array('decorate' => false);
        $tree = $repo->buildTree($nodesHierarchy, $options);

        $treeAux = array();
        foreach ($tree as $key => $value) {

           $treeAux[$key] = $value;
           $subcategories = $value['__children'];

           usort($subcategories, function ($a, $b) {
             return strcmp(ucfirst($a["name"]), ucfirst($b["name"]));
           }); 
           
           $treeAux[$key]['__children'] = $subcategories;
        }

        return $this->render('ZiiwebEcommerceBundle:Default:navbar.html.twig', array(
            'navbar' => $treeAux,
            'current_route' => $request->get('current_route'),
            'current_category_product' => $request->get('current_category_product')
        ));
    }

    /**
     * @Route("/categoria/{categoryProduct}", name="ziiweb_ecommerce_default_product_list") 
     */
    public function listProductsAction($categoryProduct) {

	$repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:CategoryProduct');
        $categoryProduct = $repo->findOneBy(array('slug' => $categoryProduct));

        $childrenHierarchy = $repo->childrenHierarchy($categoryProduct, false, array(), true);

        //get the id's of the children categories
	$res = array();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($childrenHierarchy), \RecursiveIteratorIterator::SELF_FIRST);
	foreach ($iterator as $k => $v) {
	    if ($k === 'id') {
		$res[] = $v;
	    }
	}

	$repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersion');

        $qb = $repo->createQueryBuilder('pv')
          ->join('pv.product', 'p')
          ->join('pv.productVersionSizes', 'pvs')
          ->where('p.categoryProduct IN (:categoryProduct)')
          ->andWhere('pvs.stock > 0')
          ->andWhere('pv.enabled = 1')
          ->setParameter('categoryProduct', $res);


        $query = $qb->getQuery();
        $productVersions = $query->getResult();

        return $this->render('ZiiwebEcommerceBundle:Default:product_list.html.twig', array(
            'product_versions' => $productVersions,
            'current_category_product' => $categoryProduct,
        ));
    }    

    /**
     * @Route("/producto/{product_slug}/{product_version_slug}", name="ziiweb_ecommerce_default_product_show", defaults={"product_version_slug" = null}) 
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

        return $this->render('ZiiwebEcommerceBundle:Default:product_show.html.twig', array(
            'product_version' => $productVersion,
            'general_stock' => $generalStock
        ));
    }

    /**
     * @Route("/add-to-wishlist", name="add-to-wishlist") 
     */
    public function addToWishlistAction(Request $request) {

       $product_version_id = $request->request->get('product_version_id');

       $repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersion');
       $productVersion = $repo->findOneBy(array('id' => $product_version_id));

       $productVersion->addUser($this->getUser()); 

       $em = $this->getDoctrine()->getManager();
       $em->persist($productVersion);
       $em->flush();

       $response = new JsonResponse();
       $response = $response->setData(array('status' => 'added'));

       return $response;
    }

    /**
     * @Route("/remove-from-wishlist", name="remove-from-wishlist") 
     */
    public function removeFromWishlistAction(Request $request) {

       $product_version_id = $request->request->get('product_version_id');

       $repo = $this->getDoctrine()->getRepository('DefaultBundle:User');
       $user = $repo->findOneBy(array('id' => $this->getUser()->getId()));

       $repo = $this->getDoctrine()->getRepository('ZiiwebEcommerceBundle:ProductVersion');
       $productVersion = $repo->findOneBy(array('id' => $product_version_id));
     
       $user->removeProductVersion($productVersion); 
       $productVersion->removeUser($user); 

       $em = $this->getDoctrine()->getManager();
       $em->persist($user);
       $em->flush();

       $response = new JsonResponse();
       $response = $response->setData(array('status' => 'removed'));

       return $response;
    }


    /**
     * @Route("/lista-deseos", name="ziiweb_ecommerce_default_wishlist") 
     */
    public function wishlistAction(Request $request)
    {
       return $this->render('ZiiwebEcommerceBundle:Default:product_list.html.twig');
    }

    /**
     * @Route("/novedades", name="ziiweb_ecommerce_default_featured_products") 
     */
    public function featuredAction(Request $request)
    {
       return $this->render('ZiiwebEcommerceBundle:Default:product_list.html.twig');
    }

    /**
     * @Route("/search", name="search") 
     */
    public function searchAction(Request $request) 
    {
       $keyword = $request->query->get('keyword');       

       return $this->render('ZiiwebEcommerceBundle:Default:product_list.html.twig', array(
           'keyword' => $keyword
       ));
    }

    /**
     * @Route("/aviso-legal", name="ziiweb_ecommerce_default_legal_advice") 
     */
    public function legalAdvice(Request $request) 
    {
       return $this->render('ZiiwebEcommerceBundle:Default:legal_advice.html.twig', array());
    }

    /**
     * @Route("/privacy_policiy", name="ziiweb_ecommerce_default_privacy_policy") 
     */
    public function privacyPolicy(Request $request) 
    {
       return $this->render('ZiiwebEcommerceBundle:Default:privacy_policy.html.twig', array());
    }

    /**
     * @Route("/cookies_policy", name="ziiweb_ecommerce_default_cookies_policy") 
     */
    public function cookiesPolicy(Request $request) 
    {
       return $this->render('ZiiwebEcommerceBundle:Default:cookies_policy.html.twig', array());
    }

    /**
     * @Route("/return_policy", name="ziiweb_ecommerce_default_return_policy") 
     */
    public function returnPolicy(Request $request) 
    {
       return $this->render('ZiiwebEcommerceBundle:Default:return_policy.html.twig', array());
    }
}






