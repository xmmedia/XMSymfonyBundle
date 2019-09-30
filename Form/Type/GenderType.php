<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Xm\SymfonyBundle\Model\Gender;

class GenderType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label'           => 'Gender',
            'choices'         => Gender::getValues(),
            'invalid_message' => '"{{ value }}" is an invalid gender.',
        ]);
    }

    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class;
    }
}
