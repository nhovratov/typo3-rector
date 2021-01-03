<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Namespace_\RenameNamespaceRector;
use Ssch\TYPO3Rector\Rector\v8\v6\MigrateSelectShowIconTableRector;
use Ssch\TYPO3Rector\Rector\v8\v6\MoveRequestUpdateOptionFromControlToColumnsRector;
use Ssch\TYPO3Rector\Rector\v8\v6\RefactorTCARector;
use Ssch\TYPO3Rector\Rector\v8\v6\RemoveL10nModeNoCopyRector;
use Ssch\TYPO3Rector\Rector\v8\v6\RichtextFromDefaultExtrasToEnableRichtextRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(RenameNamespaceRector::class)->call('configure', [
        RenameNamespaceRector::OLD_TO_NEW_NAMESPACES => [
            'TYPO3\CMS\Core\Tests' => 'TYPO3\TestingFramework\Core',
        ],
    ]);
    $services->set(MoveRequestUpdateOptionFromControlToColumnsRector::class);
    $services->set(RichtextFromDefaultExtrasToEnableRichtextRector::class);
    $services->set(RefactorTCARector::class);
    $services->set(MigrateSelectShowIconTableRector::class);
    $services->set(RemoveL10nModeNoCopyRector::class);
};
