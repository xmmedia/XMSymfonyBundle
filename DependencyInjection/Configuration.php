<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is very similar to to:
 * https://github.com/prooph/event-store-symfony-bundle/blob/master/src/DependencyInjection/Configuration.php#L107.
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('xm_symfony');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->append($this->addRepositoriesSection())
                ->append($this->addSessionExpirySection())
                ->append($this->addMaintenanceSection())
            ->end();

        return $treeBuilder;
    }

    private function addRepositoriesSection(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('repositories');
        $repositoriesNode = $treeBuilder->getRootNode();

        $beginsWithAt = static fn (string $v): bool => str_starts_with($v, '@');
        $removeFirstCharacter = static fn (string $v): string => substr($v, 1);

        $repositoriesNode
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
                ->scalarNode('repository_class')->end()
                ->scalarNode('aggregate_type')->isRequired()->end()
                ->scalarNode('aggregate_translator')->isRequired()
                    ->beforeNormalization()
                        ->ifTrue($beginsWithAt)
                        ->then($removeFirstCharacter)
                    ->end()
                ->end()
                ->scalarNode('stream_name')->defaultNull()->end()
                ->scalarNode('store')->defaultValue('default')->end()
            ->end();

        return $repositoriesNode;
    }

    private function addSessionExpirySection(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('session_expiry');
        $sessionExpiryNode = $treeBuilder->getRootNode();

        $sessionExpiryNode
            ->info('Signs out users idle for longer than framework.session.gc_maxlifetime.')
            ->canBeDisabled();

        return $sessionExpiryNode;
    }

    private function addMaintenanceSection(): NodeDefinition
    {
        $treeBuilder = new TreeBuilder('maintenance');
        $maintenanceNode = $treeBuilder->getRootNode();

        $maintenanceNode
            ->info(
                'While the file exists, everyone except the allowed IPs gets a 503 & messenger workers pause.'
                .' Turned on & off with app:maintenance.',
            )
            ->canBeDisabled()
            ->children()
                ->scalarNode('file')
                    ->info('Must survive deploys, eg in var/ when it\'s shared between releases.')
                    ->defaultValue('%kernel.project_dir%/var/maintenance')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('time_zone')
                    ->info('For reading & showing when it\'s expected to be over. Defaults to PHP\'s.')
                    ->defaultNull()
                ->end()
            ->end();

        return $maintenanceNode;
    }
}
