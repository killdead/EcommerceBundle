<?php
 
namespace Ziiweb\EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Ziiweb\EcommerceBundle\Entity\ProductVersion
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class ProductVersion 
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="productVersions", cascade={"persist"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     **/
    private $product;

    /**
     * @ORM\Column(type="float", name="price")
     *
     * @var string $price
     */
    protected $price;

    /**
     * @ORM\Column(type="float", name="oldPrice", nullable=true)
     *
     * @var string $price
     */
    protected $oldPrice;

    /**
     * @ORM\OneToMany(targetEntity="ProductVersionImage", mappedBy="productVersion", cascade={"persist", "remove"})
     **/
    private $productVersionImages;
  

    public function __construct() {
        $this->productVersionImages = new ArrayCollection();
    }

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
     * Set product
     *
     * @param \Ziiweb\EcommerceBundle\Entity\Product $product
     *
     * @return ProductVersion
     */
    public function setProduct(\Ziiweb\EcommerceBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \Ziiweb\EcommerceBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ProductVersion
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return ProductVersion
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set oldPrice
     *
     * @param float $oldPrice
     *
     * @return ProductVersion
     */
    public function setOldPrice($oldPrice)
    {
        $this->oldPrice = $oldPrice;

        return $this;
    }

    /**
     * Get oldPrice
     *
     * @return float
     */
    public function getOldPrice()
    {
        return $this->oldPrice;
    }

    /**
     * Add productVersionImage
     *
     * @param \Ziiweb\EcommerceBundle\Entity\ProductVersionImage $productVersionImage
     *
     * @return ProductVersion
     */
    public function addProductVersionImage(\Ziiweb\EcommerceBundle\Entity\ProductVersionImage $productVersionImage)
    {
        $this->productVersionImages[] = $productVersionImage;

        $productVersionImage->setProductVersion($this);

        return $this;
    }

    /**
     * Remove productVersionImage
     *
     * @param \Ziiweb\EcommerceBundle\Entity\ProductVersionImage $productVersionImage
     */
    public function removeProductVersionImage(\Ziiweb\EcommerceBundle\Entity\ProductVersionImage $productVersionImage)
    {
        $this->productVersionImages->removeElement($productVersionImage);

        $productVersionImage->setProductVersion(null);
    }

    /**
     * Get productVersionImages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductVersionImages()
    {
        return $this->productVersionImages;
    }
}
