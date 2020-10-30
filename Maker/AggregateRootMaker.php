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

    public function configureCommand(
        Command $command,
        InputConfiguration $inputConfig
    ) {
        $command
            ->setDescription('Creates a new model with aggregate root.')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                sprintf(
                    'Choose a name for your aggregate root (model) (e.g. <fg=yellow>%s</>)',
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
        $arName = trim($input->getArgument('name'));
        $skeletonPath = $this->skeletonPath().'model/';

        $arClassDetails = $generator->createClassNameDetails(
            $arName,
            'Model\\'.$arName.'\\'
        );
        $arLowerName = Str::asLowerCamelCase($arName);
        $idClassShortName = $arClassDetails->getShortName().'Id';
        $idClassFullName = $arClassDetails->getFullName().'Id';
        $idProperty = Str::asLowerCamelCase($arClassDetails->getShortName().'Id');
        $idField = Str::asSnakeCase($idProperty);
        $listClassName = $arClassDetails->getShortName().'List';

        $nameVoClassDetails = $generator->createClassNameDetails(
            'Name',
            'Model\\'.$arName.'\\'
        );

        $generator->generateClass(
            $arClassDetails->getFullName(),
            $skeletonPath.'Ar.tpl.php',
            [
                'id_class'    => $idClassShortName,
                'id_property' => $idProperty,
            ]
        );
        $arTestClassDetails = $generator->createClassNameDetails(
            $arName.'Test',
            'Tests\\Model\\'.$arName.'\\'
        );
        $generator->generateClass(
            $arTestClassDetails->getFullName(),
            $skeletonPath.'ArTest.tpl.php',
            [
                'model_class' => $arClassDetails->getFullName(),
                'model'       => $arClassDetails->getShortName(),
                'model_lower' => $arLowerName,
                'id_property' => $idProperty,
                'name_class'  => $nameVoClassDetails->getFullName(),
            ]
        );

        $generator->generateClass(
            $arClassDetails->getFullName().'Id',
            $skeletonPath.'ArId.tpl.php'
        );

        $arIdTestClassDetails = $generator->createClassNameDetails(
            $arName.'IdTest',
            'Tests\\Model\\'.$arName.'\\'
        );
        $generator->generateClass(
            $arIdTestClassDetails->getFullName(),
            $skeletonPath.'ArIdTest.tpl.php',
            [
                'model'          => $arClassDetails->getShortName(),
                'id_class'       => $idClassFullName,
                'id_class_short' => $idClassShortName,
                'id_property'    => $idProperty,
            ]
        );

        $listClassDetails = $generator->createClassNameDetails(
            $listClassName,
            'Model\\'.$arName.'\\'
        );
        $generator->generateClass(
            $listClassDetails->getFullName(),
            $skeletonPath.'ArList.tpl.php',
            [
                'model'          => $arClassDetails->getShortName(),
                'id_class_short' => $idClassShortName,
                'model_lower'    => $arLowerName,
            ]
        );

        $generator->generateClass(
            $nameVoClassDetails->getFullName(),
            $skeletonPath.'Vo.tpl.php'
        );

        $nameVoTestClassDetails = $generator->createClassNameDetails(
            'NameTest',
            'Tests\\Model\\'.$arName.'\\'
        );
        $generator->generateClass(
            $nameVoTestClassDetails->getFullName(),
            $skeletonPath.'VoTest.tpl.php',
            [
                'name_class' => $nameVoClassDetails->getFullName(),
            ]
        );

        $repositoryClassDetails = $generator->createClassNameDetails(
            $arName.'Repository',
            'Infrastructure\\Repository\\'
        );
        $generator->generateClass(
            $repositoryClassDetails->getFullName(),
            $skeletonPath.'Repository.tpl.php',
            [
                'id_class'       => $idClassFullName,
                'id_class_short' => $idClassShortName,
                'id_property'    => $idProperty,
                'model'          => $arClassDetails->getShortName(),
                'model_lower'    => $arLowerName,
                'list_class'     => $listClassName,
            ]
        );

        $notFoundExceptionClassDetails = $generator->createClassNameDetails(
            $arName.'NotFound',
            'Model\\'.$arName.'\\Exception\\'
        );
        $generator->generateClass(
            $notFoundExceptionClassDetails->getFullName(),
            $skeletonPath.'ExceptionNotFound.tpl.php',
            [
                'id_class'       => $idClassFullName,
                'id_class_short' => $idClassShortName,
                'id_property'    => $idProperty,
                'model'          => $arClassDetails->getShortName(),
            ]
        );

        $deletedExceptionClassDetails = $generator->createClassNameDetails(
            $arName.'IsDeleted',
            'Model\\'.$arName.'\\Exception\\'
        );
        $generator->generateClass(
            $deletedExceptionClassDetails->getFullName(),
            $skeletonPath.'ExceptionDeleted.tpl.php',
            [
                'id_class'       => $idClassFullName,
                'id_class_short' => $idClassShortName,
                'id_property'    => $idProperty,
                'model'          => $arClassDetails->getShortName(),
            ]
        );

        $commandsEvents = [
            'Create' => 'WasCreated',
            'Update' => 'WasUpdated',
            'Delete' => 'WasDeleted',
        ];
        $mutationClasses = [];
        foreach ($commandsEvents as $command => $event) {
            $commandClassDetails = $generator->createClassNameDetails(
                $command.$arName,
                'Model\\'.$arName.'\\Command\\'
            );
            $commandTemplate = 'Delete' !== $command ? 'Command.tpl.php' : 'CommandDelete.tpl.php';
            $generator->generateClass(
                $commandClassDetails->getFullName(),
                $skeletonPath.$commandTemplate,
                [
                    'id_class'       => $idClassFullName,
                    'id_class_short' => $idClassShortName,
                    'id_property'    => $idProperty,
                    'model'          => $arClassDetails->getShortName(),
                    'name_class'     => $nameVoClassDetails->getFullName(),
                ]
            );

            $commandTestClassDetails = $generator->createClassNameDetails(
                $command.$arName.'Test',
                'Tests\\Model\\'.$arName.'\\Command\\'
            );
            $commandTestTemplate = 'Delete' !== $command ? 'CommandTest.tpl.php' : 'CommandDeleteTest.tpl.php';
            $generator->generateClass(
                $commandTestClassDetails->getFullName(),
                $skeletonPath.$commandTestTemplate,
                [
                    'command_class'       => $commandClassDetails->getFullName(),
                    'command_class_short' => $commandClassDetails->getShortName(),
                    'id_property'         => $idProperty,
                    'name_class'          => $nameVoClassDetails->getFullName(),
                ]
            );

            $handlerClassDetails = $generator->createClassNameDetails(
                $command.$arName.'Handler',
                'Model\\'.$arName.'\\Handler\\'
            );
            $handlerTemplate = 'Delete' !== $command ? 'Handler.tpl.php' : 'HandlerDelete.tpl.php';
            $generator->generateClass(
                $handlerClassDetails->getFullName(),
                $skeletonPath.$handlerTemplate,
                [
                    'edit'                => 'Create' !== $command,
                    'model'               => $arClassDetails->getShortName(),
                    'list_class'          => $listClassName,
                    'repo_property'       => Str::asLowerCamelCase(
                        $arClassDetails->getShortName().'Repo'
                    ),
                    'command_class'       => $commandClassDetails->getFullName(),
                    'command_class_short' => $commandClassDetails->getShortName(),
                    'model_lower'         => $arLowerName,
                    'id_class_short'      => $idClassShortName,
                    'id_property'         => $idProperty,
                ]
            );

            $handlerTestClassDetails = $generator->createClassNameDetails(
                $command.$arName.'HandlerTest',
                'Tests\\Model\\'.$arName.'\\Handler\\'
            );
            $generator->generateClass(
                $handlerTestClassDetails->getFullName(),
                $skeletonPath.'Handler'.$command.'Test.tpl.php',
                [
                    'model'                 => $arClassDetails->getShortName(),
                    'model_class'           => $arClassDetails->getFullName(),
                    'list_class'            => $listClassDetails->getFullName(),
                    'list_class_short'      => $listClassName,
                    'command_class'         => $commandClassDetails->getFullName(),
                    'command_class_short'   => $commandClassDetails->getShortName(),
                    'handler_class'         => $handlerClassDetails->getFullName(),
                    'handler_class_short'   => $handlerClassDetails->getShortName(),
                    'model_lower'           => $arLowerName,
                    'id_class'              => $idClassFullName,
                    'id_class_short'        => $idClassShortName,
                    'id_property'           => $idProperty,
                    'name_class'            => $nameVoClassDetails->getFullName(),
                    'not_found_class'       => $notFoundExceptionClassDetails->getFullName(),
                    'not_found_class_short' => $notFoundExceptionClassDetails->getShortName(),
                ]
            );

            $eventClassDetails = $generator->createClassNameDetails(
                $arName.$event,
                'Model\\'.$arName.'\\Event\\'
            );
            $eventTemplate = 'Delete' !== $command ? 'Event.tpl.php' : 'EventDelete.tpl.php';
            $generator->generateClass(
                $eventClassDetails->getFullName(),
                $skeletonPath.$eventTemplate,
                [
                    'id_class'       => $idClassFullName,
                    'id_class_short' => $idClassShortName,
                    'id_property'    => $idProperty,
                    'model'          => $arClassDetails->getShortName(),
                    'name_class'     => $nameVoClassDetails->getFullName(),
                ]
            );

            $eventTestClassDetails = $generator->createClassNameDetails(
                $arName.$event.'Test',
                'Tests\\Model\\'.$arName.'\\Event\\'
            );
            $eventTestTemplate = 'Delete' !== $command ? 'EventTest.tpl.php' : 'EventDeleteTest.tpl.php';
            $generator->generateClass(
                $eventTestClassDetails->getFullName(),
                $skeletonPath.$eventTestTemplate,
                [
                    'event_class'       => $eventClassDetails->getFullName(),
                    'event_class_short' => $eventClassDetails->getShortName(),
                    'id_property'       => $idProperty,
                    'name_class'        => $nameVoClassDetails->getFullName(),
                ]
            );

            $mutationClassDetails = $generator->createClassNameDetails(
                $arName.$command.'Mutation',
                'Infrastructure\\GraphQl\\Mutation\\'.$arName.'\\'
            );
            $mutationClasses[$command] = $mutationClassDetails;
            $generator->generateClass(
                $mutationClassDetails->getFullName(),
                $skeletonPath.'Mutation.tpl.php',
                [
                    'delete'              => 'Delete' === $command,
                    'command_class'       => $commandClassDetails->getFullName(),
                    'command_class_short' => $commandClassDetails->getShortName(),
                    'id_class'            => $idClassFullName,
                    'id_class_short'      => $idClassShortName,
                    'id_property'         => $idProperty,
                    'id_field'            => $idField,
                    'model_lower'         => $arLowerName,
                    'name_class'          => $nameVoClassDetails->getFullName(),
                ]
            );

            $mutationTestClassDetails = $generator->createClassNameDetails(
                $arName.$command.'MutationTest',
                'Tests\\Infrastructure\\GraphQl\\Mutation\\'.$arName.'\\'
            );
            $generator->generateClass(
                $mutationTestClassDetails->getFullName(),
                $skeletonPath.'MutationTest.tpl.php',
                [
                    'delete'               => 'Delete' === $command,
                    'mutation_class'       => $mutationClassDetails->getFullName(),
                    'mutation_class_short' => $mutationClassDetails->getShortName(),
                    'command_class'        => $commandClassDetails->getFullName(),
                    'command_class_short'  => $commandClassDetails->getShortName(),
                    'id_property'          => $idProperty,
                ]
            );
        }

        $generator->generateFile(
            'config/graphql/types/domain/'.$arLowerName.'.yaml',
            $skeletonPath.'graphql_domain.tpl.php',
            [
                'model'       => $arClassDetails->getShortName(),
                'id_property' => $idProperty,
            ]
        );
        $generator->generateFile(
            'config/graphql/types/'.$arLowerName.'.mutation.yaml',
            $skeletonPath.'graphql_mutation.tpl.php',
            [
                'model'           => $arClassDetails->getShortName(),
                'model_lower'     => $arLowerName,
                'id_property'     => $idProperty,
                'mutation_create' => $this->doubleEscapeClass(
                    $mutationClasses['Create']->getFullName()
                ),
                'mutation_update' => $this->doubleEscapeClass(
                    $mutationClasses['Update']->getFullName()
                ),
                'mutation_delete' => $this->doubleEscapeClass(
                    $mutationClasses['Delete']->getFullName()
                ),
            ]
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
            sprintf(
                '- Scaffold projection: <info>bin/console maker:projection %s</info>',
                $arName,
            ),
            '- Update permissions in GraphQL config',
            '- Add ID class to UuidFakerProvider (for tests)',
            '- Update GraphQL schema: <info>bin/console app:graphql:dump-schema <username></info>',
        ]);
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            \Xm\SymfonyBundle\Messaging\Command::class,
            'xm/symfony'
        );
        $dependencies->addClassDependency(
            MessageBusInterface::class,
            'symfony/message-bus'
        );

        $dependencies->addClassDependency(
            AggregateRoot::class,
            'xm/symfony'
        );

        $dependencies->addClassDependency(
            Entity::class,
            'xm/symfony'
        );

        $dependencies->addClassDependency(
            UuidId::class,
            'xm/symfony'
        );

        $dependencies->addClassDependency(
            ValueObject::class,
            'xm/symfony'
        );

        $dependencies->addClassDependency(
            AggregateChanged::class,
            'xm/symfony'
        );

        $dependencies->addClassDependency(
            AggregateRepository::class,
            'xm/symfony'
        );
    }
}
