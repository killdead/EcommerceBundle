<?php

namespace Ziiweb\EcommerceBundle\Entity;

use Ziiweb\EcommerceBundle\Entity\ProductVersion;
use Doctrine\ORM\Mapping as ORM;

/**
 * Ziiweb\EcommerceBundle\Entity\ProductVersionPurchase
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class ProductVersionSizePurchase 
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Purchase", inversedBy="productVersionSizePurchases", cascade={"persist"})
     * @ORM\JoinColumn(name="purchase_id", referencedColumnName="id")
     **/
    private $purchase;

    /**
     * @ORM\ManyToOne(targetEntity="ProductVersionSize", inversedBy="productVersionSizePurchases", cascade={"persist"})
     * @ORM\JoinColumn(name="product_version_size_id", referencedColumnName="id")
     **/
    private $productVersionSize;

    /**
     * @ORM\Column(type="integer", name="number", nullable=false)
     *
     * @var integer $number
     */
    protected $number;

    /**
     * @ORM\Column(type="float", name="precio_total_subitem", nullable=false)
     *
     * @var float $precioTotalSubitem
     */
    protected $precioTotalSubitem;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set number
     *
     * @param integer $number
     *
     * @return ProductVersionSizePurchase
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set precioTotalSubitem
     *
     * @param float $precioTotalSubitem
     *
     * @return ProductVersionSizePurchase
     */
    public function setPrecioTotalSubitem($precioTotalSubitem)
    {
        $this->precioTotalSubitem = $precioTotalSubitem;

        return $this;
    }

    /**
     * Get precioTotalSubitem
     *
     * @return float
     */
    public function getPrecioTotalSubitem()
    {
        return $this->precioTotalSubitem;
    }

    /**
     * Set purchase
     *
     * @param \Ziiweb\EcommerceBundle\Entity\Purchase $purchase
     *
     * @return ProductVersionSizePurchase
     */
    public function setPurchase(\Ziiweb\EcommerceBundle\Entity\Purchase $purchase = null)
    {
        $this->purchase = $purchase;

        return $this;
    }

    /**
     * Get purchase
     *
     * @return \Ziiweb\EcommerceBundle\Entity\Purchase
     */
    public function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * Set productVersionSize
     *
     * @param \Ziiweb\EcommerceBundle\Entity\ProductVersionSize $productVersionSize
     *
     * @return ProductVersionSizePurchase
     */
    public function setProductVersionSize(\Ziiweb\EcommerceBundle\Entity\ProductVersionSize $productVersionSize = null)
    {
        $this->productVersionSize = $productVersionSize;

        return $this;
    }

    /**
     * Get productVersionSize
     *
     * @return \Ziiweb\EcommerceBundle\Entity\ProductVersionSize
     */
    public function getProductVersionSize()
    {
        return $this->productVersionSize;
    }
}
