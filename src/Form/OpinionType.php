<?php

namespace App\Form;

use App\Entity\Opinion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpinionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rating',null)
            ->add('description')
            ->add('author')
            ->add('created',null,['required' => false, 'disabled' => true])
            ->add('email',null,['required' => false])
            ->add('book',null,['required' => false, 'disabled' => true])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Opinion::class,
            'csrf_protection' => false,
        ]);
    }
}
