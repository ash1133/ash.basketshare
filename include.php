<?php
use Bitrix\Main\Loader;

spl_autoload_register(function ($className) {
    $namespacePrefix = 'Ash\\BasketShare\\';
    $baseDir = __DIR__ . '/lib/';

    if (strpos($className, $namespacePrefix) === 0) {
        $relativeClass = substr($className, strlen($namespacePrefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
});

Loader::registerAutoLoadClasses(
    'ash.basketshare',
    [
        'Ash\\BasketShare\\BasketShareManager' => 'lib/BasketShareManager.php',
        'Ash\\BasketShare\\BasketShareHLBlock' => 'lib/BasketShareHLBlock.php',
        'Ash\\BasketShare\\BasketShareEvent' => 'lib/BasketShareEvent.php',
    ]
);