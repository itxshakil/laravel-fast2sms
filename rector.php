<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use RectorLaravel\Rector\FuncCall\RemoveDumpDataDeadCodeRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withImportNames(removeUnusedImports: true)
    ->withComposerBased(laravel: true)

    ->withSets([
        LevelSetList::UP_TO_PHP_83,

        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::TYPE_DECLARATION_DOCBLOCKS,
    ])

    ->withConfiguredRule(RemoveDumpDataDeadCodeRector::class, [
        'dd',
        'dump',
        'var_dump',
    ])

    ->withRules([
        AddReturnTypeDeclarationRector::class,
        AddParamTypeDeclarationRector::class,
    ]);
