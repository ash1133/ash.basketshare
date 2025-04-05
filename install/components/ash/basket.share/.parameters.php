<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = [
    'GROUPS' => [
        'SETTINGS' => [
            'NAME' => Loc::getMessage('BASKET_SHARE_GROUP_SETTINGS'),
            'SORT' => 100,
        ],
    ],
    'PARAMETERS' => [
        'POPUP_ID' => [
            'PARENT' => 'SETTINGS',
            'NAME' => Loc::getMessage('BASKET_SHARE_PARAM_POPUP_ID'),
            'TYPE' => 'STRING',
            'DEFAULT' => 'basket-share-popup',
            'SORT' => 100,
        ],
        'CACHE_TIME' => ['DEFAULT' => 36000000],
    ],
];
