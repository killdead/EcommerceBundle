<?php

namespace Ziiweb\EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class ProductVersionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('price', null, array('label' => 'Precio'))
            ->add('color', null, array('label' => 'Color', 'required' => false))
            //->add('oldPrice')
            ->add('productVersionImages', CollectionType::class, array(
                'entry_type' => ProductVersionImageType::class, 
                //'label' => false
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('productVersionSizes', CollectionType::class, array(
                'entry_type' => ProductVersionSizeType::class, 
                //'label' => false
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ziiweb\EcommerceBundle\Entity\ProductVersion'
        ));
    }
}
