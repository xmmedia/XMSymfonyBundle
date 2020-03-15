<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Resolver;

use GraphQL\Type\Definition\ResolveInfo;

class FieldResolver extends \Overblog\GraphQLBundle\Resolver\FieldResolver
{
    public function __invoke($parentValue, $args, $context, ResolveInfo $info)
    {
        $fieldName = $info->fieldName;
        $value = self::valueFromObjectOrArray($parentValue, $fieldName);

        return $value instanceof \Closure ? $value($parentValue, $args, $context, $info) : $value;
    }

    public static function valueFromObjectOrArray($objectOrArray, $fieldName)
    {
        if (\is_object($objectOrArray) && \is_callable([$objectOrArray, $fieldName])) {
            return $objectOrArray->$fieldName();
        }

        return parent::valueFromObjectOrArray($objectOrArray, $fieldName);
    }
}
