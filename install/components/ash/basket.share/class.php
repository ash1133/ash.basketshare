<?php

use Ash\BasketShare\BasketShareManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Contract\Controllerable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class BasketShareComponent extends CBitrixComponent implements Controllerable
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['POPUP_ID'] = isset($arParams['POPUP_ID']) ? $arParams['POPUP_ID'] : 'basket-share-popup';

        return $arParams;
    }

    public function executeComponent()
    {
        if (!Loader::includeModule('ash.basketshare')) {
            ShowError(Loc::getMessage('BASKET_SHARE_MODULE_NOT_INSTALLED'));
            return;
        }

        $this->includeComponentTemplate();
    }

    public function configureActions()
    {
        return [
            'generateLink' => [
                'prefilters' => [],
            ],
        ];
    }

    /**
     * Действие для генерации ссылки на корзину
     */
    public function generateLinkAction()
    {
        if (!Loader::includeModule('ash.basketshare')) {
            return [
                'success' => false,
                'error' => Loc::getMessage('BASKET_SHARE_MODULE_NOT_INSTALLED'),
            ];
        }

        $BSManager = new BasketShareManager();
        $shareLink = $BSManager->createShareLink();

        if (!$shareLink) {
            return [
                'success' => false,
                'error' => Loc::getMessage('BASKET_SHARE_LINK_GENERATION_ERROR'),
            ];
        }

        return [
            'success' => true,
            'link' => $BSManager->getShortLink($shareLink),
        ];
    }
}
