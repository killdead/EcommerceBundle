<?php
 
namespace Ziiweb\EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Ziiweb\EcommerceBundle\Entity\Order;

/**
 * Ziiweb\EcommerceBundle\Entity\ProductVersionSize
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class ProductVersionSize
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ProductVersion", inversedBy="productVersionSizes", cascade={"persist"})
     * @ORM\JoinColumn(name="product_version_id", referencedColumnName="id")
     **/
    private $productVersion;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $code;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $size;

    /**
     * @ORM\Column(type="integer")
     */
    protected $stock;

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
     * Set code
     *
     * @param string $code
     *
     * @return ProductVersionSize
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set stock
     *
     * @param integer $stock
     *
     * @return ProductVersionSize
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get stock
     *
     * @return integer
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set productVersion
     *
     * @param \Ziiweb\EcommerceBundle\Entity\ProductVersion $productVersion
     *
     * @return ProductVersionSize
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
     * Set size
     *
     * @param string $size
     *
     * @return ProductVersionSize
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }
}
