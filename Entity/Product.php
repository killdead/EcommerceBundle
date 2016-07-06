<?php
 
namespace Ziiweb\EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Ziiweb\EcommerceBundle\Entity\Product
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Product 
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, name="name")
     */
    protected $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

     /**
     * @ORM\Column(type="string", length=2000, name="description", nullable=true)
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Manufacturer", inversedBy="products")
     * @ORM\JoinColumn(name="manufacturer_id", referencedColumnName="id", nullable=true)
     **/
    protected $manufacturer;

    /**
     * @ORM\ManyToOne(targetEntity="Ziiweb\EcommerceBundle\Entity\CategoryProduct", inversedBy="products")
     * @ORM\JoinColumn(name="category_product_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     **/
    private $categoryProduct;

    /**
     * @ORM\OneToMany(targetEntity="ProductVersion", mappedBy="product", cascade={"persist", "remove"})
     **/
    private $productVersions;
  
    public function __toString() 
    {
        return $this->name;
    }

    public function __construct() {
        $this->productVersions = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Product
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
     * Set description
     *
     * @param string $description
     *
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set manufacturer
     *
     * @param \Ziiweb\EcommerceBundle\Entity\Manufacturer $manufacturer
     *
     * @return Product
     */
    public function setManufacturer(\Ziiweb\EcommerceBundle\Entity\Manufacturer $manufacturer = null)
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * Get manufacturer
     *
     * @return \Ziiweb\EcommerceBundle\Entity\Manufacturer
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * Set categoryProduct
     *
     * @param \Ziiweb\EcommerceBundle\Entity\CategoryProduct $categoryProduct
     *
     * @return Product
     */
    public function setCategoryProduct(\Ziiweb\EcommerceBundle\Entity\CategoryProduct $categoryProduct = null)
    {
        $this->categoryProduct = $categoryProduct;

        return $this;
    }

    /**
     * Get categoryProduct
     *
     * @return \Ziiweb\EcommerceBundle\Entity\CategoryProduct
     */
    public function getCategoryProduct()
    {
        return $this->categoryProduct;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Product
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
     * Add productVersion
     *
     * @param \Ziiweb\EcommerceBundle\Entity\ProductVersion $productVersion
     *
     * @return Product
     */
    public function addProductVersion(\Ziiweb\EcommerceBundle\Entity\ProductVersion $productVersion)
    {
        $this->productVersions[] = $productVersion;

        $productVersion->setProduct($this);

        return $this;
    }

    /**
     * Remove productVersion
     *
     * @param \Ziiweb\EcommerceBundle\Entity\ProductVersion $productVersion
     */
    public function removeProductVersion(\Ziiweb\EcommerceBundle\Entity\ProductVersion $productVersion)
    {
        $this->productVersions->removeElement($productVersion);

        $productVersion->setProduct(null);
    }

    /**
     * Get productVersions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductVersions()
    {
        return $this->productVersions;
    }
}
