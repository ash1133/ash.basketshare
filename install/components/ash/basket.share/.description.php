<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentDescription = [
    'NAME' => Loc::getMessage('BASKET_SHARE_COMPONENT_NAME'),
    'DESCRIPTION' => Loc::getMessage('BASKET_SHARE_COMPONENT_DESCRIPTION'),
    'ICON' => '/images/icon.gif',
    'SORT' => 300,
    'CACHE_PATH' => 'Y',
    'PATH' => [
        'ID' => 'ash',
        'NAME' => Loc::getMessage('BASKET_SHARE_VENDOR_NAME'),
        'CHILD' => [
            'ID' => 'basket',
            'NAME' => Loc::getMessage('BASKET_SHARE_CATEGORY_NAME'),
        ],
    ],
];