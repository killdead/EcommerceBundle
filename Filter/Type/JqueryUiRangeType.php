<?php
namespace Ziiweb\EcommerceBundle\Filter\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JqueryUiRangeType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['min'] = $options['min'] ;
        $view->vars['max'] = $options['max'];
        $view->vars['step'] = $options['step']; 
        $view->vars['currency'] = $options['currency']; 
        $view->vars['add_taxes'] = $options['add_taxes']; 
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'min' => null,
            'max' => null,
            'step' => null,
            'currency' => null,
            'add_taxes' => null,
        ));
    }
} 
