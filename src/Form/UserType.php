<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('displayName', TextType::class)
            ->add('timezone', TimezoneType::class)
            ->add('phoneNumber', TelType::class, [
                'label' => 'Phone number (international style, e.g. +44 7700 990210)',
                'required' => false
            ])
            // This had an odd bug where it wouldn't show right on screen
            // after it had been changed, but only on the first request
            // following the change. Baffling.
            ->add('phoneNumberVerified', CheckboxType::class, [
                'label' => 'Is phone number verified for SMS sending?',
                'required' => false,
                'disabled' => true
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
