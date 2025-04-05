<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arCurrentValues */

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
        'INCLUDE_IN_BASKET' => [
            'PARENT' => 'SETTINGS',
            'NAME' => GetMessage('BASKET_SHARE_PARAM_INCLUDE_IN_BASKET'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N',
            'REFRESH' => 'Y',
        ],
        'CACHE_TIME' => ['DEFAULT' => 36000000],
    ],
];


if (!empty($arCurrentValues['INCLUDE_IN_BASKET']) && $arCurrentValues['INCLUDE_IN_BASKET'] != 'N')
{
    $arComponentParameters['PARAMETERS']['INCLUDE_IN_BASKET_SELECTOR'] = [
        'PARENT' => 'SETTINGS',
        'NAME' => Loc::getMessage('BASKET_SHARE_PARAM_INCLUDE_IN_BASKET_SELECTOR'),
        'TYPE' => 'STRING',
        'DEFAULT' => '.basket-checkout-block-btn',
        'SORT' => 100,
    ];
}