<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints as Assert;
use Xm\SymfonyBundle\Model\User\User;

class UserChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label'       => 'Current Password',
                'constraints' => [
                    new Assert\NotBlank(),
                    new UserPassword([
                        'message' => 'Please check that this matches your current password.',
                    ]),
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'label'           => 'New Password',
                'invalid_message' => 'The passwords must match.',
                'constraints'     => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => User::PASSWORD_MIN_LENGTH,
                        'max' => BasePasswordEncoder::MAX_PASSWORD_LENGTH,
                    ]),
                    new Assert\NotCompromisedPassword([
                        'threshold' => 3,
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
