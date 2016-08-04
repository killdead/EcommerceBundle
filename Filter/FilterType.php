<?php
namespace Ziiweb\EcommerceBundle\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Ziiweb\EcommerceBundle\Filter\Type\JqueryUiRangeType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class FilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        foreach ($options['filter_config'] as $key => $column) {
     
            if ($column['type'] == 'range') {
		$builder
		    ->add($column['name'], JqueryUiRangeType::class, array(
                        'min' => $column['values']['min'],
                        'max' => $column['values']['max'],
                        'step' => $column['step'], // 0.01 if we are a working with two __decimals___, 1 if we're working with no decimals
                        'add_taxes' => $column['add_taxes'],
                        'currency' => $column['currency'],
			'mapped' => false, 
			'label' => $column['label'], 
			//'attr' => $column['values'] + array('class' => 'slider-range') + array('data-symbol' => $column['add_symbol']), 
			'attr' => array('class' => 'slider-range'), 
		    ));
            } else if ($column['type'] == 'checkbox') {

		$builder
		    ->add($column['name'], ChoiceType::class, array(
			'mapped' => false, 
                        'choices' => $column['values'], 
			'expanded' => true, 
			'multiple' => true, 
			'label' => $column['label'], 
		    ));
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'filter_config' => null,
            'csrf_protection' => false,
        ));
    }
} 
