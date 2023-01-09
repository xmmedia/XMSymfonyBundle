<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Projection\ReadModelProjector;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class ProjectionMaker extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:projection';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new projection plus entity & repository/finder.';
    }

    public function configureCommand(
        Command $command,
        InputConfiguration $inputConfig
    ) {
        $command
            ->addArgument(
                'projection',
                InputArgument::OPTIONAL,
                sprintf(
                    'Choose a name of the projection (e.g. <fg=yellow>%s</>). "_projection" will be appended to the end',
                    Str::asSnakeCase(Str::getRandomTerm())
                )
            )
            ->addArgument(
                'ar',
                InputArgument::OPTIONAL,
                sprintf(
                    'What is the name of the related aggregate root (model) (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm())
                )
            )
        ;
    }

    public function generate(
        InputInterface $input,
        ConsoleStyle $io,
        Generator $generator
    ) {
        $projectionName = strtolower(trim($input->getArgument('projection')));
        $arName = trim($input->getArgument('ar'));
        $modelUpper = strtoupper(Str::asSnakeCase($arName));
        $skeletonPath = $this->skeletonPath().'projection/';

        $projectionClassName = Str::asCamelCase($projectionName);

        $arClassDetails = $generator->createClassNameDetails(
            $arName,
            'Model\\'.$arName.'\\'
        );
        $idClassShortName = $arClassDetails->getShortName().'Id';
        $idClassFullName = $arClassDetails->getFullName().'Id';
        $idProperty = Str::asLowerCamelCase($arClassDetails->getShortName().'Id');
        $idField = Str::asSnakeCase($idProperty);

        $projectionClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'Projection',
            'Projection\\'.$arName.'\\'
        );
        $generator->generateClass(
            $projectionClassDetails->getFullName(),
            $skeletonPath.'Projection.tpl.php',
            [
                'id_field'    => $idField,
                'model'       => $arClassDetails->getShortName(),
                'stream_name' => Str::asSnakeCase($arName),
            ]
        );

        $projectionTestClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'ProjectionTest',
            'Tests\\Projection\\'.$arName.'\\'
        );
        $generator->generateClass(
            $projectionTestClassDetails->getFullName(),
            $skeletonPath.'ProjectionTest.tpl.php',
            [
                'projection_class'       => $projectionClassDetails->getFullName(),
                'projection_class_short' => $projectionClassDetails->getShortName(),
                'model'                  => $arClassDetails->getShortName(),
                'stream_name'            => Str::asSnakeCase($arName),
            ]
        );

        $readModelClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'ReadModel',
            'Projection\\'.$arName.'\\'
        );
        $generator->generateClass(
            $readModelClassDetails->getFullName(),
            $skeletonPath.'ReadModel.tpl.php',
            [
                'id_field'    => $idField,
                'id_property' => $idProperty,
                'model_upper' => $modelUpper,
            ]
        );

        $readModelTestClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'ReadModelTest',
            'Tests\\Projection\\'.$arName.'\\'
        );
        $generator->generateClass(
            $readModelTestClassDetails->getFullName(),
            $skeletonPath.'ReadModelTest.tpl.php',
            [
                'read_model_class'       => $readModelClassDetails->getFullName(),
                'read_model_class_short' => $readModelClassDetails->getShortName(),
                'projection_name'        => $projectionName,
                'id_field'               => $idField,
                'id_property'            => $idProperty,

                'model_upper'            => $modelUpper,
            ]
        );

        $entityClassDetails = $generator->createClassNameDetails(
            $projectionClassName,
            'Entity\\'
        );
        $finderClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'Finder',
            'Projection\\'.$arName.'\\'
        );
        $notFoundExceptionClassDetails = $generator->createClassNameDetails(
            $arName.'NotFound',
            'Model\\'.$arName.'\\Exception\\'
        );

        $generator->generateClass(
            $finderClassDetails->getFullName(),
            $skeletonPath.'Finder.tpl.php',
            [
                'id_class'              => $idClassFullName,
                'id_class_short'        => $idClassShortName,
                'entity_class'          => $entityClassDetails->getFullName(),
                'entity_class_short'    => $entityClassDetails->getShortName(),
                'entity'                => Str::asLowerCamelCase($entityClassDetails->getShortName()),
                'not_found_class'       => $notFoundExceptionClassDetails->getFullName(),
                'not_found_class_short' => $notFoundExceptionClassDetails->getShortName(),
            ]
        );
        $generator->generateClass(
            $entityClassDetails->getFullName(),
            $skeletonPath.'Entity.tpl.php',
            [
                'id_class'       => $idClassFullName,
                'id_class_short' => $idClassShortName,
                'id_property'    => $idProperty,
                'finder_class'   => $finderClassDetails->getFullName(),
            ]
        );

        $entityTestClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'Test',
            'Tests\\Entity\\'
        );
        $generator->generateClass(
            $entityTestClassDetails->getFullName(),
            $skeletonPath.'EntityTest.tpl.php',
            [
                'entity_class'       => $entityClassDetails->getFullName(),
                'entity_class_short' => $entityClassDetails->getShortName(),
                'id_class'           => $idClassFullName,
                'id_class_short'     => $idClassShortName,
                'id_property'        => $idProperty,
            ]
        );

        $resolverClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'Resolver',
            'Infrastructure\\GraphQl\\Resolver\\'.$arName.'\\'
        );
        $generator->generateClass(
            $resolverClassDetails->getFullName(),
            $skeletonPath.'Resolver.tpl.php',
            [
                'entity_class'       => $entityClassDetails->getFullName(),
                'entity_class_short' => $entityClassDetails->getShortName(),
                'id_class'           => $idClassFullName,
                'id_class_short'     => $idClassShortName,
                'finder_class'       => $finderClassDetails->getFullName(),
                'finder_class_short' => $finderClassDetails->getShortName(),
                'finder_property'    => Str::asLowerCamelCase(
                    $finderClassDetails->getShortName()
                ),
                'id_property'        => $idProperty,
            ]
        );

        $resolverTestClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'ResolverTest',
            'Tests\\Infrastructure\\GraphQl\\Resolver\\'.$arName.'\\'
        );
        $generator->generateClass(
            $resolverTestClassDetails->getFullName(),
            $skeletonPath.'ResolverTest.tpl.php',
            [
                'resolver_class'       => $resolverClassDetails->getFullName(),
                'resolver_class_short' => $resolverClassDetails->getShortName(),
                'id_class'             => $idClassFullName,
                'id_class_short'       => $idClassShortName,
                'entity_class'         => $entityClassDetails->getFullName(),
                'entity_class_short'   => $entityClassDetails->getShortName(),
                'finder_class'         => $finderClassDetails->getFullName(),
                'finder_class_short'   => $finderClassDetails->getShortName(),
                'id_property'          => $idProperty,
            ]
        );

        $multipleResolverClassDetails = $generator->createClassNameDetails(
            Str::singularCamelCaseToPluralCamelCase($projectionClassName).'Resolver',
            'Infrastructure\\GraphQl\\Resolver\\'.$arName.'\\'
        );
        $generator->generateClass(
            $multipleResolverClassDetails->getFullName(),
            $skeletonPath.'MultipleResolver.tpl.php',
            [
                'entity_class'       => $entityClassDetails->getFullName(),
                'entity_class_short' => $entityClassDetails->getShortName(),
                'finder_class'       => $finderClassDetails->getFullName(),
                'finder_class_short' => $finderClassDetails->getShortName(),
                'finder_property'    => Str::asLowerCamelCase(
                    $finderClassDetails->getShortName()
                ),
            ]
        );

        $multipleResolverTestClassDetails = $generator->createClassNameDetails(
            Str::singularCamelCaseToPluralCamelCase($projectionClassName).'ResolverTest',
            'Tests\\Infrastructure\\GraphQl\\Resolver\\'.$arName.'\\'
        );
        $generator->generateClass(
            $multipleResolverTestClassDetails->getFullName(),
            $skeletonPath.'MultipleResolverTest.tpl.php',
            [
                'resolver_class'       => $multipleResolverClassDetails->getFullName(),
                'resolver_class_short' => $multipleResolverClassDetails->getShortName(),
                'entity_class'         => $entityClassDetails->getFullName(),
                'entity_class_short'   => $entityClassDetails->getShortName(),
                'finder_class'         => $finderClassDetails->getFullName(),
                'finder_class_short'   => $finderClassDetails->getShortName(),
            ]
        );

        $generator->generateFile(
            'config/graphql/types/'.$projectionName.'.query.yaml',
            $skeletonPath.'graphql_query.tpl.yaml',
            [
                'entity_class_short'        => $entityClassDetails->getShortName(),
                'entity_class_short_plural' => ucwords(
                    Str::singularCamelCaseToPluralCamelCase(
                        $entityClassDetails->getShortName()
                    )
                ),
                'id_property'               => $idProperty,
                'resolver_single'           => $this->doubleEscapeClass(
                    $resolverClassDetails->getFullName()
                ),
                'resolver_multiple'         => $this->doubleEscapeClass(
                    $multipleResolverClassDetails->getFullName()
                ),
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text([
            'Next:',
            sprintf(
                '- Add <info>public const %s = \'%s\';</info> to <info>App\\Projection\\Table</info>',
                strtoupper(Str::asSnakeCase($projectionName)),
                $projectionName,
            ),
            '- Add the projection to list in your <info>config/packages/prooph_event_store.yaml</info> file:',
            sprintf(
                "<info>\t%s_projection:\n\t    read_model: %s\n\t    projection: %s</info>",
                $projectionName,
                $readModelClassDetails->getFullName(),
                $projectionClassDetails->getFullName(),
            ),
            '- Add to <info>App\\Messenger\\RunProjectionMiddleware</info>:',
            sprintf(
                "<info>\tprivate const %s = '%s';</info>",
                $modelUpper,
                $projectionName.'_projection',
            ),
            '- Add to <info>App\\Messenger\\RunProjectionMiddleware::$namespaceToProjection</info>:',
            sprintf(
                "<info>\t'%s\\Event' => [\n\t    self::%s,\n\t],</info>",
                Str::getNamespace($arClassDetails->getFullName()),
                $modelUpper,
            ),
            '- Scaffold model: <info>bin/console make:model</info>',
            '- Upload files to dev server, if necessary',
            sprintf(
                '- Run projection once (optional): <info>bin/console event-store:projection:run %s_projection -o</info>',
                $projectionName,
            ),
            '- Update permissions in GraphQL config',
            '- Update GraphQL schema: <info>bin/console app:graphql:dump-schema <username></info>',
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        // @todo update
        $dependencies->addClassDependency(
            ReadModelProjection::class,
            'prooph/event-store-symfony-bundle'
        );
        $dependencies->addClassDependency(
            ReadModelProjector::class,
            'prooph/event-store'
        );

        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm-pack'
        );

        $dependencies->addClassDependency(
            ResolverInterface::class,
            'overblog/graphql-bundle'
        );
    }
}
