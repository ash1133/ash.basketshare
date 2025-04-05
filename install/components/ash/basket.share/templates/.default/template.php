<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var BasketShareComponent $component */

$this->setFrameMode(true);
$arParams['POPUP_ID'] = htmlspecialcharsbx($arParams['POPUP_ID']);
?>

<div class="basket-share-container" data-entity="<?= $arParams['POPUP_ID'] ?>-button-container">
    <button data-entity="<?= $arParams['POPUP_ID'] ?>-button" class="basket-share-button">
        <?= Loc::getMessage('BASKET_SHARE_BUTTON_TEXT') ?>
    </button>
</div>

<div id="<?= $arParams['POPUP_ID'] ?>" class="basket-share-popup" style="display: none;">
    <div class="basket-share-popup-content">
        <div class="basket-share-popup-title"><?= Loc::getMessage('BASKET_SHARE_POPUP_TITLE') ?></div>
        <div class="basket-share-popup-description"><?= Loc::getMessage('BASKET_SHARE_POPUP_DESCRIPTION') ?></div>

        <div class="basket-share-popup-link-container">
            <input type="text" id="<?= $arParams['POPUP_ID'] ?>-link" class="basket-share-popup-link" readonly>
            <button id="<?= $arParams['POPUP_ID'] ?>-copy" class="basket-share-popup-copy">
                <?= Loc::getMessage('BASKET_SHARE_COPY_BUTTON') ?>
            </button>
        </div>

        <div id="<?= $arParams['POPUP_ID'] ?>-copy-success" class="basket-share-popup-success" style="display: none;">
            <?= Loc::getMessage('BASKET_SHARE_COPY_SUCCESS') ?>
        </div>

        <div id="<?= $arParams['POPUP_ID'] ?>-loading" class="basket-share-popup-loading" style="display: none;">
            <?= Loc::getMessage('BASKET_SHARE_LOADING') ?>
        </div>

        <div id="<?= $arParams['POPUP_ID'] ?>-error" class="basket-share-popup-error" style="display: none;"></div>

    </div>
</div>

<script>
    BX.ready(function() {
        var basketShare = new BasketShare({
            componentPath: '<?= CUtil::JSEscape($componentPath) ?>',
            popupId: '<?= CUtil::JSEscape($arParams['POPUP_ID']) ?>',

            moveButtonToBasket: <?= $arParams['INCLUDE_IN_BASKET'] == 'Y' ? 'true' : 'false' ?>,
            moveButtonPlaceSelector: '<?= $arParams['INCLUDE_IN_BASKET'] == 'Y' ? $arParams['INCLUDE_IN_BASKET_SELECTOR'] : '' ?>',

            buttonSelector: '<?= CUtil::JSEscape("{$arParams['POPUP_ID']}-button")?>',
            buttonContainerSelector: '<?= CUtil::JSEscape("{$arParams['POPUP_ID']}-button-container")?>',
            linkInputId: '<?= CUtil::JSEscape("{$arParams['POPUP_ID']}-link")?>',
            copyButtonId: '<?= CUtil::JSEscape("{$arParams['POPUP_ID']}-copy")?>',
            closeButtonId: '<?= CUtil::JSEscape("{$arParams['POPUP_ID']}-close")?>',
            loadingId: '<?= CUtil::JSEscape("{$arParams['POPUP_ID']}-loading")?>',
            errorId: '<?= CUtil::JSEscape("{$arParams['POPUP_ID']}-error")?>',
            successId: '<?= CUtil::JSEscape("{$arParams['POPUP_ID']}-copy-success")?>',
            signedParameters: '<?= $this->getComponent()->getSignedParameters() ?>'
        });
    });
</script>
