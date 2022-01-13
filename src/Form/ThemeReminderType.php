<?php

namespace App\Form;

use App\Entity\ThemeReminder;
use App\Types\ReminderStyle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeReminderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enabled')
            ->add('timeOfDay', TimeType::class, [
                'label' => 'Time of day to send reminder'
            ])
            ->add('create', SubmitType::class, [
                'label' => $options['submit_label']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ThemeReminder::class,
            'submit_label' => 'Create'
        ]);
    }
}
