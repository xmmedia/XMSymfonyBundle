<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Xm\SymfonyBundle\DataProvider\CountryProvider;
use Xm\SymfonyBundle\DataProvider\ProvinceProvider;
use Xm\SymfonyBundle\Model\Address;
use Xm\SymfonyBundle\Model\PostalCode;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('line1', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Address::LINE_MIN_LENGTH,
                        'max' => Address::LINE_MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('line2', TextType::class, [
                'constraints' => [
                    new Assert\Length([
                        'min' => Address::LINE_MIN_LENGTH,
                        'max' => Address::LINE_MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('city', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => Address::CITY_MIN_LENGTH,
                        'max' => Address::CITY_MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('province', ChoiceType::class, [
                'choices'     => ProvinceProvider::all(),
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'invalid_message' => 'The province or state "{{ value }}" is not allowed.',
            ])
            ->add('postalCode', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => PostalCode::MIN_LENGTH,
                        'max' => PostalCode::MAX_LENGTH,
                    ]),
                ],
            ])
            ->add('country', ChoiceType::class, [
                'choices'     => CountryProvider::all(),
                'constraints' => [
                    new Assert\NotBlank(),
                ],
                'invalid_message' => 'The country "{{ value }}" is not an allowed country.',
            ])
        ;
    }
}
