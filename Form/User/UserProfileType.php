<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Xm\SymfonyBundle\Form\Type\EmailType;
use Xm\SymfonyBundle\Model\User\Name;
use Xm\SymfonyBundle\Validator\Constraints\UniqueCurrentUsersEmail;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new UniqueCurrentUsersEmail(),
                ],
            ])
            ->add('firstName', TextType::class, [
                'label'       => 'First Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Name::MIN_LENGTH,
                        'max' => Name::MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label'       => 'Last Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Name::MIN_LENGTH,
                        'max' => Name::MAX_LENGTH,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
