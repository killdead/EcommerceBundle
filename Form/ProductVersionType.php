<?php

namespace Ziiweb\EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProductVersionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('colorCode', null, array('label' => 'Color en web (por ejemplo escribe "negras" para unas zapatillas negras)', 'required' => false))
            ->add('enabled', null, array('label' => 'Visible', 'required' => false))
            ->add('featured', null, array('label' => 'Destacado', 'required' => false))


            ->add('price', null, array('label' => 'Precio (sin I.V.A.)', 'attr' => array('class' => 'price', 'data-tax-rate' => \Ziiweb\EcommerceBundle\Entity\TaxRates::VAT_RATE + 1)))
            ->add('price_plus_taxes', TextType::class, array('label' => 'Precio (con I.V.A.)', 'mapped' => false, 'required' => false, 'attr' => array('class' => 'price_plus_taxes', 'data-tax-rate' => \Ziiweb\EcommerceBundle\Entity\TaxRates::VAT_RATE + 1)))

            ->add('salePrice', null, array('label' => 'Precio rebajado (sin I.V.A.)', 'attr' => array('class' => 'price', 'required' => false, 'data-tax-rate' => \Ziiweb\EcommerceBundle\Entity\TaxRates::VAT_RATE + 1)))
            ->add('sale_amount_plus_taxes', TextType::class, array('label' => 'Precio rebajado (con I.V.A.)', 'mapped' => false, 'required' => false, 'attr' => array('class' => 'sale_amount_plus_taxes', 'data-tax-rate' => \Ziiweb\EcommerceBundle\Entity\TaxRates::VAT_RATE + 1)))


            ->add('color', null, array('label' => 'Color en filtros de busqueda (poner "multicolor" si tienes varios colores. Nombre de color en masculino. Atención acentos!!!!)', 'required' => false))
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
