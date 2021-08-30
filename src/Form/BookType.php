<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isbn',TextType::class, ['disabled' => $options['is_edit']])
            ->add('title')
            ->add('description')
            ->add('created',DateType::class, ['required' => false,'disabled' => true ])
            ->add('author',null, ['required' => false, 'disabled' => true])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
            'csrf_protection' => false,
            'is_edit' => false,
        ]);
    }
}
