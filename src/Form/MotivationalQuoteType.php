<?php

namespace App\Form;

use App\Entity\MotivationalQuote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MotivationalQuoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quote')
            ->add('attribution')
            ->add('create', SubmitType::class, [
                'label' => $options['submit_label']
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MotivationalQuote::class,
            'submit_label' => 'Create'
        ]);
    }
}
