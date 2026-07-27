<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;

return RectorConfig::configure()
    ->withParallel()
    ->withPreparedSets(
        codeQuality: true,
        codingStyle: true,
        // no privatization, naming, instanceOf, and earlyReturn
        // no deadCode and typeDeclarations as they're below
    )
    ->withAttributesSets(symfony: true, doctrine: true)
    ->withComposerBased(doctrine: true, phpunit: true, symfony: true)
    ->withPhpSets()
    ->withTypeCoverageLevel(29)
    ->withDeadCodeLevel(40)
    ->withPaths([
        __DIR__.'/Command',
        __DIR__.'/DataFixtures',
        __DIR__.'/DataProvider',
        __DIR__.'/DependencyInjection',
        __DIR__.'/Doctrine',
        __DIR__.'/EventSourcing',
        __DIR__.'/EventStore',
        __DIR__.'/EventSubscriber',
        __DIR__.'/Exception',
        __DIR__.'/Infrastructure',
        __DIR__.'/Maker',
        __DIR__.'/Messaging',
        __DIR__.'/Messenger',
        __DIR__.'/Migrations',
        __DIR__.'/Model',
        __DIR__.'/Resources',
        __DIR__.'/Security',
        __DIR__.'/Tests',
        __DIR__.'/Util',
        __DIR__.'/XmSymfonyBundle.php',
    ])
    ->withSets([
        PHPUnitSetList::PHPUNIT_90,
    ])
    ->withSkip([
        // the skeleton files are templates, not valid PHP
        __DIR__.'/Resources/skeleton',
        // we may not want the property to have a default value
        Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector::class,
        Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector::class,
        // from set "codingStyle"
        Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector::class,
        Rector\CodingStyle\Rector\If_\NullableCompareToNullRector::class,
        Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector::class,
        Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector::class,
        Rector\CodingStyle\Rector\String_\SimplifyQuoteEscapeRector::class,
        // from set "codeQuality"
        Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector::class,
        // from set "deadCode"
        Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector::class,
        // temporarily disabled because it adds newlines between traits
        Rector\CodingStyle\Rector\ClassLike\NewlineBetweenClassLikeStmtsRector::class,
    ])
;
