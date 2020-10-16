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

        $generator->generateClass(
            $arClassDetails->getFullName(),
            $skeletonPath.'Ar.tpl.php',
            [
                'id_class'    => $idClassShortName,
                'id_property' => $idProperty,
            ]
        );

        $generator->generateClass(
            $arClassDetails->getFullName().'Id',
            $skeletonPath.'ArId.tpl.php'
        );
        $generator->generateClass(
            $arClassDetails->getFullName().'List',
            $skeletonPath.'ArList.tpl.php',
            [
                'model'       => $arClassDetails->getShortName(),
                'id_class'    => $idClassShortName,
                'model_lower' => $arLowerName,
            ]
        );

        $nameVoClassDetails = $generator->createClassNameDetails(
            'Name',
            'Model\\'.$arName.'\\'
        );
        $generator->generateClass(
            $nameVoClassDetails->getFullName(),
            $skeletonPath.'Vo.tpl.php'
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
            'Add'    => 'WasAdded',
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

            $handlerClassDetails = $generator->createClassNameDetails(
                $command.$arName.'Handler',
                'Model\\'.$arName.'\\Handler\\'
            );
            $handlerTemplate = 'Delete' !== $command ? 'Handler.tpl.php' : 'HandlerDelete.tpl.php';
            $generator->generateClass(
                $handlerClassDetails->getFullName(),
                $skeletonPath.$handlerTemplate,
                [
                    'edit'               => 'Add' !== $command,
                    'model'              => $arClassDetails->getShortName(),
                    'list_class'         => $listClassName,
                    'repo_property'      => Str::asLowerCamelCase(
                        $arClassDetails->getShortName().'Repo'
                    ),
                    'command_full_class' => $commandClassDetails->getFullName(),
                    'command_class'      => $commandClassDetails->getShortName(),
                    'model_lower'        => $arLowerName,
                    'id_class_short'     => $idClassShortName,
                    'id_property'        => $idProperty,
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
                'mutation_add'    => $this->doubleEscapeClass(
                    $mutationClasses['Add']->getFullName()
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
