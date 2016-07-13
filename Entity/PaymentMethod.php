<?php

namespace Ziiweb\EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;


/**
 * Ziiweb\EcommerceBundle\Entity\PaymentMethod
 *
 * @ORM\Table
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class PaymentMethod
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="nombre")
     */
    protected $nombre;

    /**
     * @ORM\Column(type="float", name="precio")
     */
    protected $precio;

    /**
     * @ORM\Column(type="float", name="porcentaje")
     */
    protected $porcentaje;

    
    public function __construct() {
    }

    public function __toString() {

      return $this->nombre;
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
     * Set nombre
     *
     * @param string $nombre
     * @return PaymentMethod
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set precio
     *
     * @param float $precio
     * @return PaymentMethod
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;

        return $this;
    }

    /**
     * Get precio
     *
     * @return float 
     */
    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * Set porcentaje
     *
     * @param float $porcentaje
     *
     * @return PaymentMethod
     */
    public function setPorcentaje($porcentaje)
    {
        $this->porcentaje = $porcentaje;

        return $this;
    }

    /**
     * Get porcentaje
     *
     * @return float
     */
    public function getPorcentaje()
    {
        return $this->porcentaje;
    }
}
