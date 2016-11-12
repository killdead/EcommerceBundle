<?php

namespace BillingBundle\Form\Type;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MyNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('number', TextType::class);

        $builder->get('number')
            ->addModelTransformer(new CallbackTransformer(
                function ($number) {
                    // transform the array to a string
                    //var_dump($number);
                    return number_format($number, 2, ',', '.');
                },
                function ($tagsAsString) {
                    // transform the string back to an array
                    return explode(', ', $tagsAsString);
                }
            ))
        ;
    }

    // 
