<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class TaskPostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [new NotBlank(allowNull: false), new Length(min: 3, max: 255)],
            ])
            ->add('description', TextType::class, [
                'constraints' => [new NotBlank(allowNull: true), new Length(min:3)],
            ])
            ->add('deadline', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new NotBlank(allowNull: true)],
            ])
            ->add('completed', CheckboxType::class, [
                'constraints' => [new Type(type: 'bool')],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
