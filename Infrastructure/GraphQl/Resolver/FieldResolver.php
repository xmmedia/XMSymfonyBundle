<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use JetBrains\PhpStorm\Deprecated;

/**
 * @deprecated
 */
#[Deprecated]
class FieldResolver
{
    /**
     * Allowed method prefixes.
     */
    protected const PREFIXES = ['get', 'is', 'has', ''];

    public function __invoke($parentValue, $args, $context, ResolveInfo $info): mixed
    {
        trigger_deprecation('xm/symfony-bundle', '2.0.9', 'FieldResolver is deprecated and will be removed in 3.0.0. Use the default FieldResolver from overblog/graphql-bundle instead.');

        $value = self::valueFromObjectOrArray($parentValue, $info->fieldName);

        return $value instanceof \Closure ? $value($parentValue, $args, $context, $info) : $value;
    }

    public static function valueFromObjectOrArray($objectOrArray, string $fieldName): mixed
    {
        trigger_deprecation('xm/symfony-bundle', '2.0.9', 'FieldResolver is deprecated and will be removed in 3.0.0. Use the default FieldResolver from overblog/graphql-bundle instead.');

        if (\is_object($objectOrArray) && \is_callable([$objectOrArray, $fieldName])) {
            return $objectOrArray->$fieldName();
        }

        if (\is_array($objectOrArray) && isset($objectOrArray[$fieldName])) {
            return $objectOrArray[$fieldName];
        }

        if (\is_object($objectOrArray)) {
            foreach (static::PREFIXES as $prefix) {
                $method = $prefix.str_replace('_', '', $fieldName);

                if (\is_callable([$objectOrArray, $method])) {
                    return $objectOrArray->$method();
                }
            }

            if (isset($objectOrArray->$fieldName)) {
                return $objectOrArray->$fieldName;
            }
        }

        return null;
    }
}
