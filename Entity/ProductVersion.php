<?php
 
namespace Ziiweb\EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Ziiweb\EcommerceBundle\Entity\Order;
use Ziiweb\EcommerceBundle\Entity\ProductVersionSizes;
use DefaultBundle\Entity\User;

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
     * @ORM\ManyToMany(targetEntity="DefaultBundle\Entity\User", inversedBy="productVersions")
     * @ORM\JoinTable(name="wishlist")
     */
    private $users;

    /**
     * @ORM\Column(type="float", name="price")
     *
     * @var string $price
     */
    protected $price;

    /**
     * @ORM\Column(type="float", name="sale_price")
     *
     * @var string $salePrice
     */
    protected $salePrice;

    /**
     * @ORM\Column(type="string", name="color", nullable=true)
     *
     * @var string $color
     */
    protected $color;

    /**
     * @ORM\Column(type="string", name="color_code", nullable=true)
     *
     * @var string $colorCode
     */
    protected $colorCode;

    /**
     * @ORM\Column(type="boolean", name="enabled", nullable=true)
     *
     * @var string $enabled
     */
    protected $enabled = true;
 
    /**
     * @ORM\Column(type="boolean", name="featured", nullable=true)
     *
     * @var string $featured
     */
    protected $featured;

    /**
     * @Gedmo\Slug(fields={"color"})
     * @ORM\Column(length=128, nullable=true)
     */
    private $slug;

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

    /**
     * @ORM\OneToMany(targetEntity="ProductVersionSize", mappedBy="productVersion", cascade={"persist", "remove"})
     **/
    private $productVersionSizes;
  

    public function __construct() {
        $this->productVersionImages = new ArrayCollection();
        $this->productVersionSizes = new ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set salePrice
     *
     * @param float $salePrice
     *
     * @return ProductVersion
     */
    public function setSalePrice($salePrice)
    {
        $this->salePrice = $salePrice;

        return $this;
    }

    /**
     * Get salePrice
     *
     * @return float
     */
    public function getSalePrice()
    {
        return $this->salePrice;
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

    /**
     * Add productVersionSize
     *
     * @param \Ziiweb\EcommerceBundle\Entity\ProductVersionSize $productVersionSize
     *
     * @return ProductVersion
     */
    public function addProductVersionSize(\Ziiweb\EcommerceBundle\Entity\ProductVersionSize $productVersionSize)
    {
        $this->productVersionSizes[] = $productVersionSize;

        $productVersionSize->setProductVersion($this);

        return $this;
    }

    /**
     * Remove productVersionSize
     *
     * @param \Ziiweb\EcommerceBundle\Entity\ProductVersionSize $productVersionSize
     */
    public function removeProductVersionSize(\Ziiweb\EcommerceBundle\Entity\ProductVersionSize $productVersionSize)
    {
        $this->productVersionSizes->removeElement($productVersionSize);
    }

    /**
     * Get productVersionSizes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductVersionSizes()
    {
        return $this->productVersionSizes;
    }

    /**
     * Set color
     *
     * @param string $color
     *
     * @return ProductVersion
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return ProductVersion
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return ProductVersion
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set featured
     *
     * @param boolean $featured
     *
     * @return ProductVersion
     */
    public function setFeatured($featured)
    {
        $this->featured = $featured;

        return $this;
    }

    /**
     * Get featured
     *
     * @return boolean
     */
    public function getFeatured()
    {
        return $this->featured;
    }

    /**
     * Add user
     *
     * @param \DefaultBundle\Entity\User $user
     *
     * @return ProductVersion
     */
    public function addUser(\DefaultBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \DefaultBundle\Entity\User $user
     */
    public function removeUser(\DefaultBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set colorCode
     *
     * @param string $colorCode
     *
     * @return ProductVersion
     */
    public function setColorCode($colorCode)
    {
        $this->colorCode = $colorCode;

        return $this;
    }

    /**
     * Get colorCode
     *
     * @return string
     */
    public function getColorCode()
    {
        return $this->colorCode;
    }
}
