<?php
 
namespace Ziiweb\EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Ziiweb\EcommerceBundle\Entity\ProductVersionImage
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class ProductVersionImage
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ProductVersion", inversedBy="productVersionImages", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="product_version_id", referencedColumnName="id")
     **/
    private $productVersion;

    /**
     * @ORM\Column(type="string", length=255, name="file", nullable=true)
     *
     * @Assert\File(
     *     maxSize = "40M",
     *     mimeTypes= {"image/jpeg", "image/png"},
     *     mimeTypesMessage= "Archivo no válido. Los archivos deben ser .jpg o .png.",
     * )
     */
    protected $file;


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
     * Set file
     *
     * @param string $file
     *
     * @return ProductVersionImage
     */
    public function setFile($file)
    {

        if ($file !== null) {
	  $this->file = $file;

	  return $this;
        }
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set productVersion
     *
     * @param \Ziiweb\EcommerceBundle\Entity\ProductVersion $productVersion
     *
     * @return ProductVersionImage
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
}
