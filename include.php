<?php
use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    'ash.basketshare',
    [
        'Ash\\BasketShare\\Base\\BasketShareBase' => 'lib/base/basketsharebase.php',
        'Ash\\BasketShare\\BasketShareManager' => 'lib/basketsharemanager.php',
        'Ash\\BasketShare\\BasketShareHLBlock' => 'lib/basketsharehlblock.php',
        'Ash\\BasketShare\\BasketShareEvent' => 'lib/basketshareevent.php',
        'Ash\\BasketShare\\Controller\\BasketShare' => 'lib/controller/basketshare.php',
    ]
);