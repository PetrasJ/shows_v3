<?php

namespace App\Form;

use App\Entity\Feedback;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('post')
            ->add('name', TextType::class, [
                'label' => 'name',
                'attr' => [
                    'placeholder' => 'name',
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'email',
                'attr' => [
                    'placeholder' => 'email',
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'message',
                'attr' => [
                    'placeholder' => 'message',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'send',
            ])
        ;
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix(): string
    {
        return '';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Feedback::class,
        ]);
    }
}
