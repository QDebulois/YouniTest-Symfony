<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class LogoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('logo', FileType::class, [
                "required" => true,
                "attr" => [
                    "class" => "form-control"
                ],
                'constraints' => [
                    new File([
                        "maxSize" => "4096k",
                        'mimeTypes' => [
                            "image/jpeg",
                        ],
                    ])
                ],
            ])
            ->add('submit', SubmitType::class, [
                "label" => "Uploader",
                "attr" => [
                    "class" => "btn btn-success"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
