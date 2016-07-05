<?php
 
namespace Ziiweb\EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Ziiweb\EcommerceBundle\Entity\Order
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="Ziiweb\EcommerceBundle\Entity\Repository\OrderRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Order 
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
