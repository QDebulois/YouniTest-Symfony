<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                "required" => true,
            ])
            //->add('roles')
            ->add('password', PasswordType::class, [
                "required" => true,
            ])
            //->add('created_at')
            ->add("isAdmin", CheckboxType::class, [
                "mapped" => false,
                "required" => false,
                "row_attr" => [
                    "class" => "alert alert-warning"
                ],
                "label" => "Pour faciliter le process: Est Admin ?",
                "label_attr" => [
                    "class" => "me-2"
                ]

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
