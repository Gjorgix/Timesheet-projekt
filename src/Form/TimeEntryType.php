<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\TimeEntry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimeEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'name',
                'query_builder' => function ($repository) {
                    return $repository
                        ->createQueryBuilder('p')
                        ->where('p.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('p.name', 'ASC');
                },
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Data rozpoczęcia',
                'widget' => 'single_text',
                'mapped' => false,
            ])
            ->add('startTime', TextType::class, [
                'label' => 'Godzina rozpoczęcia (HH:MM)',
                'mapped' => false,
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Data zakończenia',
                'widget' => 'single_text',
                'mapped' => false,
            ])
            ->add('endTime', TextType::class, [
                'label' => 'Godzina zakończenia (HH:MM)',
                'mapped' => false,
            ])
            ->add('comment')

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TimeEntry::class,
        ]);
    }
}
