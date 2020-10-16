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

    public function configureCommand(
        Command $command,
        InputConfiguration $inputConfig
    ) {
        $command
            ->setDescription('Creates a new projection plus entity & repository/finder.')
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
                'model_upper' => strtoupper(Str::asSnakeCase($arName)),
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

        $generator->generateClass(
            $finderClassDetails->getFullName(),
            $skeletonPath.'Finder.tpl.php',
            [
                'id_class'           => $idClassFullName,
                'id_class_short'     => $idClassShortName,
                'entity_class'       => $entityClassDetails->getFullName(),
                'entity_class_short' => $entityClassDetails->getShortName(),
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

        $generator->generateFile(
            'config/graphql/types/'.$projectionName.'.query.yaml',
            $skeletonPath.'graphql_query.tpl.php',
            [
                'entity_class_short'        => $entityClassDetails->getShortName(),
                'entity_class_short_plural' => Str::singularCamelCaseToPluralCamelCase(
                    $entityClassDetails->getShortName()
                ),
                'id_property'               => $idProperty,
                'resolver_single'           => $this->doubleEscapeClass(
                    $resolverClassDetails->getFullName()
                ),
                'resolver_multiple'         => $this->doubleEscapeClass(
                    $resolverClassDetails->getFullName()
                ),
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text([
            'Next:',
            sprintf(
                '- Add <info>public const %s = \'%s_projection\';</info> to <info>App\\Projection\\Table</info>',
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
            '- Add to <info>App\\Messenger\\RunProjectionMiddleware::$namespaceToProjection</info>:',
            sprintf(
                "<info>\t'%s\\Event' => [\n\t    '%s_projection',\n\t],</info>",
                Str::getNamespace($arClassDetails->getFullName()),
                $projectionName,
            ),
            sprintf(
                '- Run projection once (optional): <info>bin/console event-store:projection:run %s_projection -o</info>',
                $projectionName,
            ),
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
