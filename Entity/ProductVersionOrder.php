<?php

namespace Ziiweb\EcommerceBundle\Entity;

use Ziiweb\EcommerceBundle\Entity\ProductVersion;
use Doctrine\ORM\Mapping as ORM;

/**
 * Ziiweb\EcommerceBundle\Entity\ProductVersionOrder
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class ProductVersionOrder 
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ProductVersion")
     * @ORM\JoinColumn(name="product_version_id", referencedColumnName="id")
     */
    private $productVersion;

    /**
     * @ORM\ManyToOne(targetEntity="Order")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;

    /**
     * @ORM\Column(type="integer")
     */
    protected $qty;
     

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
     * Set qty
     *
     * @param integer $qty
     *
     * @return ProductVersionOrder
     */
    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * Get qty
     *
     * @return integer
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * Set productVersion
     *
     * @param \Ziiweb\EcommerceBundle\Entity\ProductVersion $productVersion
     *
     * @return ProductVersionOrder
     */
    public function setProductVersion(\Ziiweb\EcommerceBundle\Entity\ProductVersion $productVersion = null)
    {
        $this->productVersion = $productVersion;

        return $this;
    }

    /**
     * Get productVersion
     *
     * @return \Ziiweb\EcommerceBundle\Entity\ProductVersion
     */
    public function getProductVersion()
    {
        return $this->productVersion;
    }

    /**
     * Set order
     *
     * @param \Ziiweb\EcommerceBundle\Entity\Order $order
     *
     * @return ProductVersionOrder
     */
    public function setOrder(\Ziiweb\EcommerceBundle\Entity\Order $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return \Ziiweb\EcommerceBundle\Entity\Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
