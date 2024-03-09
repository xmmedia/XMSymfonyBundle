<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
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
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRepository;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRoot;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateTranslator;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\Entity;
use Xm\SymfonyBundle\Model\UuidId;
use Xm\SymfonyBundle\Model\ValueObject;

class AggregateRootMaker extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:model';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a new model with aggregate root.';
    }

    public function configureCommand(
        Command $command,
        InputConfiguration $inputConfig,
    ): void {
        $command
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                sprintf(
                    'Choose a name for your aggregate root (model) (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm()),
                ),
            )
            ->addArgument(
                'entity',
                InputArgument::OPTIONAL,
                sprintf(
                    'Enter the name of the related entity (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm()),
                ),
            )
            ->addArgument(
                'projection',
                InputArgument::OPTIONAL,
                sprintf(
                    'Choose a name of the projection (e.g. <fg=yellow>%s</>). "_projection" will be appended to the end',
                    Str::asSnakeCase(Str::getRandomTerm()),
                ),
            )
        ;
    }

    public function generate(
        InputInterface $input,
        ConsoleStyle $io,
        Generator $generator,
    ): void {
        $arName = trim($input->getArgument('name'));
        $modelUpper = strtoupper(Str::asSnakeCase($arName));
        $entityName = trim($input->getArgument('entity'));
        $projectionName = strtolower(trim($input->getArgument('projection')));

        $projectionClassName = Str::asCamelCase($projectionName);

        $arClassDetails = $generator->createClassNameDetails(
            $arName,
            'Model\\'.$arName.'\\',
        );
        $arTestClassDetails = $generator->createClassNameDetails(
            $arName.'Test',
            'Tests\\Model\\'.$arName.'\\',
        );
        $arLowerName = Str::asLowerCamelCase($arName);

        $idClass = $generator->createClassNameDetails(
            $arName.'Id',
            'Model\\'.$arName.'\\',
        );
        $idProperty = Str::asLowerCamelCase($idClass->getShortName());

        $listClassName = $arClassDetails->getShortName().'List';
        $listClassDetails = $generator->createClassNameDetails(
            $listClassName,
            'Model\\'.$arName.'\\',
        );

        $entityClassDetails = $generator->createClassNameDetails(
            $entityName,
            'Entity\\',
        );
        $entityTestClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'Test',
            'Tests\\Entity\\',
        );
        $entityFinder = $generator->createClassNameDetails(
            $entityName.'Finder',
            'Projection\\'.$arName.'\\',
        );

        $nameVoClassDetails = $generator->createClassNameDetails(
            $arName.'Name',
            'Model\\'.$arName.'\\',
        );
        $nameVoTestClassDetails = $generator->createClassNameDetails(
            $arName.'NameTest',
            'Tests\\Model\\'.$arName.'\\',
        );

        $repositoryClassDetails = $generator->createClassNameDetails(
            $arName.'Repository',
            'Infrastructure\\Repository\\',
        );
        $repositoryClassTestDetails = $generator->createClassNameDetails(
            $arName.'RepositoryTest',
            'Tests\\Infrastructure\\Repository\\',
        );

        $projectionClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'Projection',
            'Projection\\'.$arName.'\\',
        );
        $readModelClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'ReadModel',
            'Projection\\'.$arName.'\\',
        );

        $queryClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'Query',
            'GraphQl\\Query\\'.$arName.'\\',
        );
        $queryTestClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'QueryTest',
            'Tests\\GraphQl\\Query\\'.$arName.'\\',
        );
        $multipleQueryClassDetails = $generator->createClassNameDetails(
            Str::singularCamelCaseToPluralCamelCase($projectionClassName).'Query',
            'GraphQl\\Query\\'.$arName.'\\',
        );
        $multipleQueryTestClassDetails = $generator->createClassNameDetails(
            Str::singularCamelCaseToPluralCamelCase($projectionClassName).'QueryTest',
            'Tests\\GraphQl\\Query\\'.$arName.'\\',
        );

        $filtersClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'Filters',
            'Projection\\'.$arName.'\\',
        );
        $queryBuilderClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'FilterQueryBuilder',
            'Projection\\'.$arName.'\\',
        );
        $countQueryClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'CountQuery',
            'GraphQl\\Query\\'.$arName.'\\',
        );

        $canBeDeletedClassInterface = $generator->createClassNameDetails(
            $arName.'CanBeDeleted',
            'Model\\'.$arName.'\\Service\\',
        );
        $canBeDeletedClassService = $generator->createClassNameDetails(
            $arName.'CanBeDeleted',
            'Infrastructure\\Service\\',
        );
        $canBeDeletedQueryClass = $generator->createClassNameDetails(
            $arName.'CanBeDeletedQuery',
            'GraphQl\\Query\\'.$arName.'\\',
        );

        $notFoundExceptionClassDetails = $generator->createClassNameDetails(
            $arName.'NotFound',
            'Model\\'.$arName.'\\Exception\\',
        );
        $cannotBeDeletedClassDetails = $generator->createClassNameDetails(
            $arName.'CannotBeDeleted',
            'Model\\'.$arName.'\\Exception\\',
        );

        $variables = [
            'can_be_deleted_interface_class'       => $canBeDeletedClassInterface->getFullName(),
            'can_be_deleted_interface_class_short' => $canBeDeletedClassInterface->getShortName(),
            'cannot_be_deleted_class'              => $cannotBeDeletedClassDetails->getFullName(),
            'cannot_be_deleted_class_short'        => $cannotBeDeletedClassDetails->getShortName(),
            'entity'                               => Str::asLowerCamelCase($entityClassDetails->getShortName()),
            'entity_class'                         => $entityClassDetails->getFullName(),
            'entity_class_short'                   => $entityClassDetails->getShortName(),
            'entity_class_short_plural'            => ucwords(
                Str::singularCamelCaseToPluralCamelCase($entityClassDetails->getShortName()),
            ),
            'entity_filter_class_short'            => $entityFinder->getShortName(),
            'entity_finder_class'                  => $entityFinder->getFullName(),
            'entity_finder_property'               => Str::asLowerCamelCase($entityFinder->getShortName()),
            'filters_class'                        => $filtersClassDetails->getFullName(),
            'filters_class_short'                  => $filtersClassDetails->getShortName(),
            'id_class'                             => $idClass->getFullName(),
            'id_class_short'                       => $idClass->getShortName(),
            'id_field'                             => Str::asSnakeCase($idProperty),
            'id_property'                          => $idProperty,
            'list_class'                           => $listClassDetails->getFullName(),
            'list_class_short'                     => $listClassDetails->getShortName(),
            'model'                                => $arClassDetails->getShortName(),
            'model_class'                          => $arClassDetails->getFullName(),
            'model_lower'                          => $arLowerName,
            'model_upper'                          => $modelUpper,
            'name_class'                           => $nameVoClassDetails->getFullName(),
            'name_class_short'                     => $nameVoClassDetails->getShortName(),
            'name_field'                           => Str::asSnakeCase($nameVoClassDetails->getShortName()),
            'name_property'                        => Str::asLowerCamelCase($nameVoClassDetails->getShortName()),
            'not_found_class'                      => $notFoundExceptionClassDetails->getFullName(),
            'not_found_class_short'                => $notFoundExceptionClassDetails->getShortName(),
            'projection_class'                     => $projectionClassDetails->getFullName(),
            'projection_class_short'               => $projectionClassDetails->getShortName(),
            'projection_name'                      => $projectionName,
            'projection_name_first_letter'         => substr($projectionName, 0, 1),
            'query_builder_class'                  => $queryBuilderClassDetails->getFullName(),
            'query_builder_class_short'            => $queryBuilderClassDetails->getShortName(),
            'query_count'                          => $this->doubleEscapeClass($countQueryClassDetails->getFullName()),
            'query_count_class'                    => $countQueryClassDetails->getFullName(),
            'query_multiple'                       => $this->doubleEscapeClass(
                $multipleQueryClassDetails->getFullName(),
            ),
            'query_multiple_class'                 => $multipleQueryClassDetails->getFullName(),
            'query_multiple_class_short'           => $multipleQueryClassDetails->getShortName(),
            'query_single'                         => $this->doubleEscapeClass($queryClassDetails->getFullName()),
            'query_single_class'                   => $queryClassDetails->getFullName(),
            'query_single_class_short'             => $queryClassDetails->getShortName(),
            'read_model_class'                     => $readModelClassDetails->getFullName(),
            'read_model_class_short'               => $readModelClassDetails->getShortName(),
            'repository_class'                     => $repositoryClassDetails->getFullName(),
            'repository_class_short'               => $repositoryClassDetails->getShortName(),
            'stream_name'                          => Str::asSnakeCase($arName),
        ];

        /*
         * Generate the files:
         */
        // **********************************************************
        // Aggregate Root
        // **********************************************************
        $generator->generateClass(
            $arClassDetails->getFullName(),
            $this->skeletonPath().'model/Ar.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $arTestClassDetails->getFullName(),
            $this->skeletonPath().'model/ArTest.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $idClass->getFullName(),
            $this->skeletonPath().'model/ArId.tpl.php',
        );
        $arIdTestClassDetails = $generator->createClassNameDetails(
            $idClass->getShortName().'Test',
            'Tests\\Model\\'.$arName.'\\',
        );
        $generator->generateClass(
            $arIdTestClassDetails->getFullName(),
            $this->skeletonPath().'model/ArIdTest.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $listClassDetails->getFullName(),
            $this->skeletonPath().'model/ArList.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $nameVoClassDetails->getFullName(),
            $this->skeletonPath().'model/Vo.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $nameVoTestClassDetails->getFullName(),
            $this->skeletonPath().'model/VoTest.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $repositoryClassDetails->getFullName(),
            $this->skeletonPath().'model/Repository.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $repositoryClassTestDetails->getFullName(),
            $this->skeletonPath().'model/RepositoryTest.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $notFoundExceptionClassDetails->getFullName(),
            $this->skeletonPath().'model/ExceptionNotFound.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $cannotBeDeletedClassDetails->getFullName(),
            $this->skeletonPath().'model/ExceptionCannotBeDeleted.tpl.php',
            $variables,
        );

        $deletedExceptionClassDetails = $generator->createClassNameDetails(
            $arName.'IsDeleted',
            'Model\\'.$arName.'\\Exception\\',
        );
        $generator->generateClass(
            $deletedExceptionClassDetails->getFullName(),
            $this->skeletonPath().'model/ExceptionDeleted.tpl.php',
            $variables,
        );

        $commandsEvents = [
            'Add'    => 'WasAdded',
            'Change' => 'WasChanged',
            'Delete' => 'WasDeleted',
        ];
        $mutationClasses = [];
        foreach ($commandsEvents as $command => $event) {
            $commandClassDetails = $generator->createClassNameDetails(
                $command.$arName,
                'Model\\'.$arName.'\\Command\\',
            );
            $commandTemplate = 'Delete' !== $command ? 'Command.tpl.php' : 'CommandDelete.tpl.php';
            $generator->generateClass(
                $commandClassDetails->getFullName(),
                $this->skeletonPath().'model/'.$commandTemplate,
                $variables,
            );

            $commandTestClassDetails = $generator->createClassNameDetails(
                $command.$arName.'Test',
                'Tests\\Model\\'.$arName.'\\Command\\',
            );
            $commandTestTemplate = 'Delete' !== $command ? 'CommandTest.tpl.php' : 'CommandDeleteTest.tpl.php';
            $generator->generateClass(
                $commandTestClassDetails->getFullName(),
                $this->skeletonPath().'model/'.$commandTestTemplate,
                [
                    ...$variables,
                    'command_class'       => $commandClassDetails->getFullName(),
                    'command_class_short' => $commandClassDetails->getShortName(),
                ],
            );

            $handlerClassDetails = $generator->createClassNameDetails(
                $command.$arName.'Handler',
                'Model\\'.$arName.'\\Handler\\',
            );
            $handlerTemplate = 'Delete' !== $command ? 'Handler.tpl.php' : 'HandlerDelete.tpl.php';
            $generator->generateClass(
                $handlerClassDetails->getFullName(),
                $this->skeletonPath().'model/'.$handlerTemplate,
                [
                    ...$variables,
                    'edit'                => 'Add' !== $command,
                    'repo_property'       => Str::asLowerCamelCase($arClassDetails->getShortName().'Repo'),
                    'command_class'       => $commandClassDetails->getFullName(),
                    'command_class_short' => $commandClassDetails->getShortName(),
                ],
            );

            $handlerTestClassDetails = $generator->createClassNameDetails(
                $command.$arName.'HandlerTest',
                'Tests\\Model\\'.$arName.'\\Handler\\',
            );
            $generator->generateClass(
                $handlerTestClassDetails->getFullName(),
                $this->skeletonPath().'model/Handler'.$command.'Test.tpl.php',
                [
                    ...$variables,
                    'command_class'       => $commandClassDetails->getFullName(),
                    'command_class_short' => $commandClassDetails->getShortName(),
                    'handler_class'       => $handlerClassDetails->getFullName(),
                    'handler_class_short' => $handlerClassDetails->getShortName(),
                ],
            );

            $eventClassDetails = $generator->createClassNameDetails(
                $arName.$event,
                'Model\\'.$arName.'\\Event\\',
            );
            $generator->generateClass(
                $eventClassDetails->getFullName(),
                $this->skeletonPath().'model/Event'.$event.'.tpl.php',
                $variables,
            );

            $eventTestClassDetails = $generator->createClassNameDetails(
                $arName.$event.'Test',
                'Tests\\Model\\'.$arName.'\\Event\\',
            );
            $generator->generateClass(
                $eventTestClassDetails->getFullName(),
                $this->skeletonPath().'model/Event'.$event.'Test.tpl.php',
                [
                    ...$variables,
                    'event_class'       => $eventClassDetails->getFullName(),
                    'event_class_short' => $eventClassDetails->getShortName(),
                ],
            );

            $mutationClassDetails = $generator->createClassNameDetails(
                $arName.$command.'Mutation',
                'GraphQl\\Mutation\\'.$arName.'\\',
            );
            $mutationClasses[$command] = $mutationClassDetails;
            $generator->generateClass(
                $mutationClassDetails->getFullName(),
                $this->skeletonPath().'model/Mutation.tpl.php',
                [
                    ...$variables,
                    'delete'              => 'Delete' === $command,
                    'add'                 => 'Add' === $command,
                    'command_class'       => $commandClassDetails->getFullName(),
                    'command_class_short' => $commandClassDetails->getShortName(),
                ],
            );

            $mutationTestClassDetails = $generator->createClassNameDetails(
                $arName.$command.'MutationTest',
                'Tests\\GraphQl\\Mutation\\'.$arName.'\\',
            );
            $generator->generateClass(
                $mutationTestClassDetails->getFullName(),
                $this->skeletonPath().'model/MutationTest.tpl.php',
                [
                    ...$variables,
                    'delete'               => 'Delete' === $command,
                    'add'                  => 'Add' === $command,
                    'mutation_class'       => $mutationClassDetails->getFullName(),
                    'mutation_class_short' => $mutationClassDetails->getShortName(),
                    'command_class'        => $commandClassDetails->getFullName(),
                    'command_class_short'  => $commandClassDetails->getShortName(),
                ],
            );
        }

        $generator->generateFile(
            'config/graphql/types/domain/'.Str::asSnakeCase($arLowerName).'.yaml',
            $this->skeletonPath().'model/graphql_domain.tpl.yaml',
            [
                ...$variables,
                'model'          => $arClassDetails->getShortName(),
                'can_be_deleted' => $this->doubleEscapeClass($canBeDeletedQueryClass->getFullName()),
            ],
        );
        $generator->generateFile(
            'config/graphql/types/'.Str::asSnakeCase($arLowerName).'.mutation.yaml',
            $this->skeletonPath().'model/graphql_mutation.tpl.yaml',
            [
                ...$variables,
                'mutation_add'    => $this->doubleEscapeClass($mutationClasses['Add']->getFullName()),
                'mutation_change' => $this->doubleEscapeClass($mutationClasses['Change']->getFullName()),
                'mutation_delete' => $this->doubleEscapeClass($mutationClasses['Delete']->getFullName()),
            ],
        );

        $generator->generateClass(
            $canBeDeletedClassInterface->getFullName(),
            $this->skeletonPath().'model/CanBeDeletedInterface.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $canBeDeletedClassService->getFullName(),
            $this->skeletonPath().'model/CanBeDeletedService.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $canBeDeletedQueryClass->getFullName(),
            $this->skeletonPath().'model/CanBeDeletedQuery.tpl.php',
            $variables,
        );

        // **********************************************************
        // Projection
        // **********************************************************
        $generator->generateClass(
            $projectionClassDetails->getFullName(),
            $this->skeletonPath().'projection/Projection.tpl.php',
            $variables,
        );

        $projectionTestClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'ProjectionTest',
            'Tests\\Projection\\'.$arName.'\\',
        );
        $generator->generateClass(
            $projectionTestClassDetails->getFullName(),
            $this->skeletonPath().'projection/ProjectionTest.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $readModelClassDetails->getFullName(),
            $this->skeletonPath().'projection/ReadModel.tpl.php',
            $variables,
        );

        $readModelTestClassDetails = $generator->createClassNameDetails(
            $projectionClassName.'ReadModelTest',
            'Tests\\Projection\\'.$arName.'\\',
        );
        $generator->generateClass(
            $readModelTestClassDetails->getFullName(),
            $this->skeletonPath().'projection/ReadModelTest.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $entityFinder->getFullName(),
            $this->skeletonPath().'projection/Finder.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $entityClassDetails->getFullName(),
            $this->skeletonPath().'projection/Entity.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $entityTestClassDetails->getFullName(),
            $this->skeletonPath().'projection/EntityTest.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $queryClassDetails->getFullName(),
            $this->skeletonPath().'projection/Query.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $queryTestClassDetails->getFullName(),
            $this->skeletonPath().'projection/QueryTest.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $multipleQueryClassDetails->getFullName(),
            $this->skeletonPath().'projection/MultipleQuery.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $multipleQueryTestClassDetails->getFullName(),
            $this->skeletonPath().'projection/MultipleQueryTest.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $filtersClassDetails->getFullName(),
            $this->skeletonPath().'projection/Filters.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $queryBuilderClassDetails->getFullName(),
            $this->skeletonPath().'projection/QueryBuilder.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $countQueryClassDetails->getFullName(),
            $this->skeletonPath().'projection/CountQuery.tpl.php',
            $variables,
        );

        $generator->generateFile(
            'config/graphql/types/'.$projectionName.'.query.yaml',
            $this->skeletonPath().'projection/graphql_query.tpl.yaml',
            $variables,
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $modelListServiceName = Str::asSnakeCase($arLowerName.'_list');
        $io->text([
            'Next:',
            '- Add the repository to list in your <info>config/packages/event_sourcing.yaml</info> file:',
            sprintf(
                "<info>\t%s:\n\t    repository_class:     %s\n\t    aggregate_type:       %s\n\t    aggregate_translator: %s\n\t    stream_name:          '%s'</info>",
                $modelListServiceName,
                $repositoryClassDetails->getFullName(),
                $arClassDetails->getFullName(),
                AggregateTranslator::class,
                Str::asSnakeCase($arLowerName),
            ),
            sprintf(
                '- Also add the repository service <info>%s: \'@%s\'</info> to your <info>config/packages/event_sourcing.yaml</info> file',
                $arClassDetails->getFullName().'List',
                $modelListServiceName,
            ),
            '- Add the projection to list in your <info>config/packages/prooph_event_store.yaml</info> file:',
            sprintf(
                "<info>\t%s_projection:\n\t    read_model: %s\n\t    projection: %s</info>",
                $projectionName,
                $readModelClassDetails->getFullName(),
                $projectionClassDetails->getFullName(),
            ),
            sprintf(
                '- Add <info>public const %s = \'%s\';</info> to <info>App\\Projection\\Table</info>',
                strtoupper(Str::asSnakeCase($projectionName)),
                $projectionName,
            ),
            '- Add to <info>App\\Messenger\\RunProjectionMiddleware</info>:',
            sprintf(
                "<info>\tprivate const %s = '%s';</info>",
                $modelUpper,
                $projectionName.'_projection',
            ),
            '- Also add the following to <info>App\\Messenger\\RunProjectionMiddleware::$namespaceToProjection</info>:',
            sprintf(
                "<info>\t'%s\\Event' => [\n\t    self::%s,\n\t],</info>",
                Str::getNamespace($arClassDetails->getFullName()),
                $modelUpper,
            ),
            '- Add event to <info>RunProjectionMiddlewareTest::messageDataProvider</info>',
            sprintf(
                '- Create the stream: <info>bin/console event-store:event-stream:create %s</info>',
                Str::asSnakeCase($arLowerName),
            ),
            sprintf(
                '- Run projection once (optional): <info>bin/console event-store:projection:run %s_projection -o</info>',
                $projectionName,
            ),
            '- Add ID class to UuidFakerProvider (for tests)',
            '- Update permissions in GraphQL config',
            '- Update GraphQL schema: <info>bin/console app:graphql:dump-schema <username></info>',
            '- Run code checks and tests',
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        // @todo update
        $dependencies->addClassDependency(
            \Xm\SymfonyBundle\Messaging\Command::class,
            'xm/symfony',
        );
        $dependencies->addClassDependency(
            MessageBusInterface::class,
            'symfony/message-bus',
        );

        $dependencies->addClassDependency(
            AggregateRoot::class,
            'xm/symfony',
        );

        $dependencies->addClassDependency(
            Entity::class,
            'xm/symfony',
        );

        $dependencies->addClassDependency(
            UuidId::class,
            'xm/symfony',
        );

        $dependencies->addClassDependency(
            ValueObject::class,
            'xm/symfony',
        );

        $dependencies->addClassDependency(
            AggregateChanged::class,
            'xm/symfony',
        );

        $dependencies->addClassDependency(
            AggregateRepository::class,
            'xm/symfony',
        );

        $dependencies->addClassDependency(
            ReadModelProjection::class,
            'prooph/event-store-symfony-bundle',
        );
        $dependencies->addClassDependency(
            ReadModelProjector::class,
            'prooph/event-store',
        );

        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm-pack',
        );

        $dependencies->addClassDependency(
            QueryInterface::class,
            'overblog/graphql-bundle',
        );
    }
}
