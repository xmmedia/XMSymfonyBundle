<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Maker;

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
        return 'Creates a new model with aggregate root. (Make Projection first.)';
    }

    public function configureCommand(
        Command $command,
        InputConfiguration $inputConfig,
    ): void {
        $command
            ->setHelp('Note: it\'s best to create the projection first using make:projection.')
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
        ;
    }

    public function generate(
        InputInterface $input,
        ConsoleStyle $io,
        Generator $generator,
    ): void {
        $arName = trim($input->getArgument('name'));
        $entityName = trim($input->getArgument('entity'));
        $skeletonPath = $this->skeletonPath().'model/';

        $arClassDetails = $generator->createClassNameDetails(
            $arName,
            'Model\\'.$arName.'\\',
        );
        $arTestClassDetails = $generator->createClassNameDetails(
            $arName.'Test',
            'Tests\\Model\\'.$arName.'\\',
        );
        $arLowerName = Str::asLowerCamelCase($arName);
        $idClassShortName = $arClassDetails->getShortName().'Id';
        $idClassFullName = $arClassDetails->getFullName().'Id';
        $idProperty = Str::asLowerCamelCase($arClassDetails->getShortName().'Id');
        $idField = Str::asSnakeCase($idProperty);
        $listClassName = $arClassDetails->getShortName().'List';
        $listClassDetails = $generator->createClassNameDetails(
            $listClassName,
            'Model\\'.$arName.'\\',
        );

        $entityClassDetails = $generator->createClassNameDetails(
            $entityName,
            'Entity\\',
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

        $notFoundExceptionClassDetails = $generator->createClassNameDetails(
            $arName.'NotFound',
            'Model\\'.$arName.'\\Exception\\',
        );

        $variables = [
            'entity'                 => Str::asLowerCamelCase($entityClassDetails->getShortName()),
            'entity_class'           => $entityClassDetails->getFullName(),
            'entity_class_short'     => $entityClassDetails->getShortName(),
            'entity_finder'          => $entityFinder->getShortName(),
            'entity_finder_class'    => $entityFinder->getFullName(),
            'entity_finder_lower'    => Str::asLowerCamelCase($entityFinder->getShortName()),
            'id_class'               => $idClassFullName,
            'id_class_short'         => $idClassShortName,
            'id_field'               => $idField,
            'id_property'            => $idProperty,
            'list_class'             => $listClassDetails->getFullName(),
            'list_class_short'       => $listClassDetails->getShortName(),
            'model'                  => $arClassDetails->getShortName(),
            'model_class'            => $arClassDetails->getFullName(),
            'model_lower'            => $arLowerName,
            'name_class'             => $nameVoClassDetails->getFullName(),
            'name_class_short'       => $nameVoClassDetails->getShortName(),
            'name_property'          => Str::asLowerCamelCase($nameVoClassDetails->getShortName()),
            'not_found_class'        => $notFoundExceptionClassDetails->getFullName(),
            'not_found_class_short'  => $notFoundExceptionClassDetails->getShortName(),
            'repository_class'       => $repositoryClassDetails->getFullName(),
            'repository_class_short' => $repositoryClassDetails->getShortName(),
        ];

        $generator->generateClass(
            $arClassDetails->getFullName(),
            $skeletonPath.'Ar.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $arTestClassDetails->getFullName(),
            $skeletonPath.'ArTest.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $arClassDetails->getFullName().'Id',
            $skeletonPath.'ArId.tpl.php',
        );
        $arIdTestClassDetails = $generator->createClassNameDetails(
            $arName.'IdTest',
            'Tests\\Model\\'.$arName.'\\',
        );
        $generator->generateClass(
            $arIdTestClassDetails->getFullName(),
            $skeletonPath.'ArIdTest.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $listClassDetails->getFullName(),
            $skeletonPath.'ArList.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $nameVoClassDetails->getFullName(),
            $skeletonPath.'Vo.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $nameVoTestClassDetails->getFullName(),
            $skeletonPath.'VoTest.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $repositoryClassDetails->getFullName(),
            $skeletonPath.'Repository.tpl.php',
            $variables,
        );
        $generator->generateClass(
            $repositoryClassTestDetails->getFullName(),
            $skeletonPath.'RepositoryTest.tpl.php',
            $variables,
        );

        $generator->generateClass(
            $notFoundExceptionClassDetails->getFullName(),
            $skeletonPath.'ExceptionNotFound.tpl.php',
            $variables,
        );

        $deletedExceptionClassDetails = $generator->createClassNameDetails(
            $arName.'IsDeleted',
            'Model\\'.$arName.'\\Exception\\',
        );
        $generator->generateClass(
            $deletedExceptionClassDetails->getFullName(),
            $skeletonPath.'ExceptionDeleted.tpl.php',
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
                $skeletonPath.$commandTemplate,
                $variables,
            );

            $commandTestClassDetails = $generator->createClassNameDetails(
                $command.$arName.'Test',
                'Tests\\Model\\'.$arName.'\\Command\\',
            );
            $commandTestTemplate = 'Delete' !== $command ? 'CommandTest.tpl.php' : 'CommandDeleteTest.tpl.php';
            $generator->generateClass(
                $commandTestClassDetails->getFullName(),
                $skeletonPath.$commandTestTemplate,
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
                $skeletonPath.$handlerTemplate,
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
                $skeletonPath.'Handler'.$command.'Test.tpl.php',
                [
                    ...$variables,
                    'command_class'         => $commandClassDetails->getFullName(),
                    'command_class_short'   => $commandClassDetails->getShortName(),
                    'handler_class'         => $handlerClassDetails->getFullName(),
                    'handler_class_short'   => $handlerClassDetails->getShortName(),
                ],
            );

            $eventClassDetails = $generator->createClassNameDetails(
                $arName.$event,
                'Model\\'.$arName.'\\Event\\',
            );
            $generator->generateClass(
                $eventClassDetails->getFullName(),
                $skeletonPath.'Event'.$event.'.tpl.php',
                $variables,
            );

            $eventTestClassDetails = $generator->createClassNameDetails(
                $arName.$event.'Test',
                'Tests\\Model\\'.$arName.'\\Event\\',
            );
            $generator->generateClass(
                $eventTestClassDetails->getFullName(),
                $skeletonPath.'Event'.$event.'Test.tpl.php',
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
                $skeletonPath.'Mutation.tpl.php',
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
                $skeletonPath.'MutationTest.tpl.php',
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
            $skeletonPath.'graphql_domain.tpl.yaml',
            [
                ...$variables,
                'model' => $arClassDetails->getShortName(),
            ],
        );
        $generator->generateFile(
            'config/graphql/types/'.Str::asSnakeCase($arLowerName).'.mutation.yaml',
            $skeletonPath.'graphql_mutation.tpl.yaml',
            [
                ...$variables,
                'mutation_add'    => $this->doubleEscapeClass($mutationClasses['Add']->getFullName()),
                'mutation_change' => $this->doubleEscapeClass($mutationClasses['Change']->getFullName()),
                'mutation_delete' => $this->doubleEscapeClass($mutationClasses['Delete']->getFullName()),
            ],
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
                '- Add the repository service <info>%s: \'@%s\'</info> to your <info>config/packages/event_sourcing.yaml</info> file',
                $arClassDetails->getFullName().'List',
                $modelListServiceName,
            ),
            sprintf(
                '- Create the stream: <info>bin/console event-store:event-stream:create %s</info>',
                Str::asSnakeCase($arLowerName),
            ),
            '- Update permissions in GraphQL config',
            '- Add ID class to UuidFakerProvider (for tests)',
            '- Update GraphQL schema: <info>bin/console app:graphql:dump-schema <username></info>',
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
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
    }
}
