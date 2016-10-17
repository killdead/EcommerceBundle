<?php

namespace Ziiweb\EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShippingDataType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array('label' => 'Nombre y apellidos *', 'required' => true))
            ->add('address', null, array('label' => 'Dirección *', 'required' => true))
            ->add('town', null, array('label' => 'Población *', 'required' => true))
            ->add('province', null, array('label' => 'Provincia *', 'required' => true))
            ->add('postal_code', null, array('label' => 'Código postal *', 'required' => true))
            ->add('country', null, array('label' => 'País *', 'required' => true))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DefaultBundle\Entity\User'
        ));
    }
}
