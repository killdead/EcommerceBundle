<?php
 
namespace Ziiweb\EcommerceBundle\Entity;

use Ziiweb\EcommerceBundle\Entity\ProductVersion;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Ziiweb\EcommerceBundle\Entity\Purchase
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Purchase
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Ziiweb\UserBundle\Entity\User", inversedBy="purchases")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     **/
    private $user;

    /**
     * @ORM\Column(type="float", name="subtotal")
     */
    private $subtotal;

    /**
     * @ORM\Column(type="float", name="iva")
     */
    private $iva;

    /**
     * @ORM\Column(type="float", name="tasa_iva")
     */
    private $tasa_iva;

    /**
     * @ORM\Column(type="float", name="re", nullable=true)
     */
    private $re;

    /**
     * @ORM\Column(type="float", name="tasa_re", nullable=true)
     */
    private $tasa_re;

    /**
     * @ORM\Column(type="float", name="contrareembolso")
     */
    private $contrareembolso;

    /**
     * @ORM\Column(type="float", name="total")
     */
    private $total;

    /**
     * @ORM\ManyToOne(targetEntity="Ziiweb\EcommerceBundle\Entity\PaymentMethod")
     * @ORM\JoinColumn(name="payment_method_id", referencedColumnName="id")
     **/
    private $metodoPago;

    /**
     * @ORM\ManyToOne(targetEntity="Ziiweb\EcommerceBundle\Entity\ShippingMethod")
     * @ORM\JoinColumn(name="shipping_method_id", referencedColumnName="id")
     **/
    private $metodoEnvio;

    /**
     * @ORM\Column(type="text", length=5000, name="notas", nullable=true)
     *
     * @var text $notas
     */
    private $notas;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var \DateTime $contentChanged
     *
     * @ORM\Column(name="content_changed", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="change", field={"title", "body"})
     */
    private $contentChanged;

    /**
     * @ORM\Column(type="string", name="name", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", name="company", nullable=true)
     */
    private $company;

    /**
     * @ORM\Column(type="string", name="address", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", name="town", nullable=true)
     */
    private $town;

    /**
     * @ORM\Column(type="string", name="postal_code", nullable=true)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", name="province", nullable=true)
     */
    private $province;

    /**
     * @ORM\Column(type="string", name="shopName", nullable=true)
     */
    private $shopName;

    /**
     * @ORM\Column(type="string", name="phone", nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", name="timetable", nullable=true)
     */
    private $timetable;


    public function __construct() {
        $this->productVersionSizePurchase = new ArrayCollection();
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
     * @ORM\OneToMany(targetEntity="ProductVersionSizePurchase", mappedBy="purchase", cascade={"persist", "remove"})
     **/
    private $productVersionSizePurchases;


    /**
     * Add pedidoSubitems
     *
     */
    public function addProductVersionSizePurchase($productVersionSizePurchases)
    {
        $productVersionSizePurchases->setPurchase($this);
        $this->productVersionSizePurchases[] = $productVersionSizePurchases;

        return $this;
    }

    /**
     * Remove pedidoSubitems
     *
     */
    public function removeProductVersionSizePurchase($productVersionSizePurchases)
    {
        $this->productVersionSizePurchases->removeElement($productVersionSizePurchases);
    }

    /**
     * Get pedidoSubitems
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProductVersionSizePurchases()
    {
        return $this->productVersionSizePurchases;
    }






    /**
     * Set user
     *
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set notas
     *
     * @param string $notas
     */
    public function setNotas($notas)
    {
        $this->notas = $notas;

        return $this;
    }

    /**
     * Get notas
     *
     * @return string 
     */
    public function getNotas()
    {
        return $this->notas;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set contentChanged
     *
     * @param \DateTime $contentChanged
     */
    public function setContentChanged($contentChanged)
    {
        $this->contentChanged = $contentChanged;

        return $this;
    }

    /**
     * Get contentChanged
     *
     * @return \DateTime 
     */
    public function getContentChanged()
    {
        return $this->contentChanged;
    }

    /** 
     *  @ORM\PrePersist 
     */
    public function doStuffOnPrePersist()
    {
        $this->created = new \DateTime("now"); 
    }

    /** 
     *  @ORM\PreUpdate 
     */
    public function doStuffOnPreUpdate()
    {
        $this->updated = new \DateTime("now"); 
    }

    /**
     * Set subtotal
     *
     * @param float $subtotal
     */
    public function setSubtotal($precio)
    {
        $this->subtotal = $precio;

        return $this;
    }

    /**
     * Get subtotal
     *
     * @return float 
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * Set iva
     *
     * @param float $iva
     */
    public function setIva($iva)
    {
        $this->iva = $iva;

        return $this;
    }

    /**
     * Get iva
     *
     * @return float 
     */
    public function getIva()
    {
        return $this->iva;
    }

    /**
     * Set total
     *
     * @param float $total
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return float 
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set metodoEnvio
     *
     * @param boolean $metodoEnvio
     */
    public function setMetodoEnvio($metodoEnvio)
    {
        $this->metodoEnvio = $metodoEnvio;

        return $this;
    }

    /**
     * Get metodoEnvio
     *
     * @return boolean 
     */
    public function getMetodoEnvio()
    {
        return $this->metodoEnvio;
    }

    /**
     * Set metodoPago
     *
     * @param boolean $metodoPago
     */
    public function setMetodoPago($metodoPago)
    {
        $this->metodoPago = $metodoPago;

        return $this;
    }

    /**
     * Get metodoPago
     *
     * @return boolean 
     */
    public function getMetodoPago()
    {
        return $this->metodoPago;
    }

    /**
     * Set descuentoContrareembolso
     *
     * @param float $descuentoContrareembolso
     */
    public function setDescuentoContrareembolso($descuentoContrareembolso)
    {
        $this->descuentoContrareembolso = $descuentoContrareembolso;

        return $this;
    }

    /**
     * Get descuentoContrareembolso
     *
     * @return float 
     */
    public function getDescuentoContrareembolso()
    {
        return $this->descuentoContrareembolso;
    }

    /**
     * Set gastosEnvio
     *
     * @param float $gastosEnvio
     */
    public function setGastosEnvio($gastosEnvio)
    {
        $this->gastosEnvio = $gastosEnvio;

        return $this;
    }

    /**
     * Get gastosEnvio
     *
     * @return float 
     */
    public function getGastosEnvio()
    {
        return $this->gastosEnvio;
    }

    /**
     * Set company
     *
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string 
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set name
     *
     * @param string $name
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
     * Set address
     *
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set town
     *
     * @param string $town
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * Get town
     *
     * @return string 
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Set postalCode
     *
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode
     *
     * @return string 
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set province
     *
     * @param string $province
     */
    public function setProvince($province)
    {
        $this->province = $province;

        return $this;
    }

    /**
     * Get province
     *
     * @return string 
     */
    public function getProvince()
    {
        return $this->province;
    }

    /**
     * Set tasaIva
     *
     * @param float $tasaIva
     *
     */
    public function setTasaIva($tasaIva)
    {
        $this->tasa_iva = $tasaIva;

        return $this;
    }

    /**
     * Get tasaIva
     *
     * @return float
     */
    public function getTasaIva()
    {
        return $this->tasa_iva;
    }

    /**
     * Set re
     *
     * @param float $re
     *
     */
    public function setRe($re)
    {
        $this->re = $re;

        return $this;
    }

    /**
     * Get re
     *
     * @return float
     */
    public function getRe()
    {
        return $this->re;
    }

    /**
     * Set tasaRe
     *
     * @param float $tasaRe
     *
     */
    public function setTasaRe($tasaRe)
    {
        $this->tasa_re = $tasaRe;

        return $this;
    }

    /**
     * Get tasaRe
     *
     * @return float
     */
    public function getTasaRe()
    {
        return $this->tasa_re;
    }

    /**
     * Set contrareembolso
     *
     * @param float $contrareembolso
     */
    public function setContrareembolso($contrareembolso)
    {
        $this->contrareembolso = $contrareembolso;

        return $this;
    }

    /**
     * Get contrareembolso
     *
     * @return float
     */
    public function getContrareembolso()
    {
        return $this->contrareembolso;
    }

    /**
     * Set shopName
     *
     * @param string $shopName
     */
    public function setShopName($shopName)
    {
        $this->shopName = $shopName;

        return $this;
    }

    /**
     * Get shopName
     *
     * @return string 
     */
    public function getShopName()
    {
        return $this->shopName;
    }

    /**
     * Set phone
     *
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set timetable
     *
     * @param string $timetable
     */
    public function setTimetable($timetable)
    {
        $this->timetable = $timetable;

        return $this;
    }

    /**
     * Get timetable
     *
     * @return string 
     */
    public function getTimetable()
    {
        return $this->timetable;
    }
}
