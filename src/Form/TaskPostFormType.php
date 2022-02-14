<?php

namespace App\Form;

use App\Entity\Task;
use DateTimeImmutable;
use PHPUnit\Framework\Constraint\IsNull;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;

class TaskPostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
               'constraints' => [new Length(min: 3, max: 255)]
            ])
            ->add('description', TextType::class, [
                'constraints' => []
            ])
            ->add('completed', CheckboxType::class, [
                'constraints' => [new Type('boolean')]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}