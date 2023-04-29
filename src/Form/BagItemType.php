<?php

namespace App\Form;

use App\Entity\BagItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BagItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder 
            //->add('quantity')
            ->add('remove', ButtonType::class, [
                'attr' => [
                    'class' => 'btn btn-sm btn-danger',
                    'onclick' => 'javascript:event.preventDefault(); var value = parseInt(document.getElementById("bag_item_quantity").value); value = isNaN(value) ? 0 : value; if(value > 0){document.getElementById("bag_item_quantity").value = value - 1;}'
                ],
                'label' => '-'
            ])
            ->add('quantity', NumberType::class ,[
                'label' => false,
                'attr' => [
                    'min' => 0,
                    'value' => 1,
                    // 'max' => $product->getStock(),
                    'max' => $options['max_quantity'],
                    'step' => 1,
                    'inputmode' => 'none',
                    'input' => 'false',
                ]
            ])
            //->add('date')
            //->add('ProductId')
            //->add('bagId')

            ->add('add', ButtonType::class, [
                'attr' => [
                    'class' => 'btn btn-sm btn-success',
                    //'onclick' => 'javascript:event.preventDefault(); document.getElementById("bag_item_quantity").value++;'
                'onclick' => 'javascript:event.preventDefault(); var value = parseInt(document.getElementById("bag_item_quantity").value); if (value < ' . $options['max_quantity'] . ') { document.getElementById("bag_item_quantity").value = value + 1; }'
                ],
                'label' => '+'
            ])
            
            // ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BagItem::class,
            'max_quantity' => null,
        ]);
    }
}
