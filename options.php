<?php
global $APPLICATION;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

$module_id = 'ash.basketshare';
$ashBasketShareRight = $APPLICATION->GetGroupRight($module_id);
Loc::loadMessages(__FILE__);
Loader::includeModule($module_id);

$request = HttpApplication::getInstance()->getContext()->getRequest();

$tabControl = new CAdminTabControl('tabControl', [
    [
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('BASKETSHARE_OPTIONS_TAB_NAME'),
        'TITLE' => Loc::getMessage('BASKETSHARE_OPTIONS_TAB_TITLE'),
    ],
    [
        'DIV' => 'edit2',
        'TAB' => Loc::getMessage('BASKETSHARE_OPTIONS_TAB_RIGHTS'),
        'TITLE' => Loc::getMessage('BASKETSHARE_OPTIONS_TAB_RIGHTS_TITLE'),
    ],
]);

if ($request->isPost() && $request['Update'] && check_bitrix_sessid()) {
    $linkLifetime = (int)$request['link_lifetime'];

    Option::set($module_id, 'link_lifetime', $linkLifetime);
    Option::set($module_id, 'basket_link', $request['basket_link']);

    // Сохранение прав доступа
    $obModule = CModule::CreateModuleObject($module_id);
}

$linkLifetime = Option::get($module_id, 'link_lifetime', 7);
$basketLink = Option::get($module_id, 'basket_link', '/personal/cart/');

$tabControl->Begin();
?>

<form method="post" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($module_id) ?>&lang=<?= LANGUAGE_ID ?>">
    <?= bitrix_sessid_post() ?>
    <?php $tabControl->BeginNextTab(); ?>

    <tr>
        <td width="40%">
            <label for="link_lifetime"><?= Loc::getMessage('BASKETSHARE_OPTIONS_LINK_LIFETIME') ?>:</label>
        </td>
        <td width="60%">
            <input type="number" name="link_lifetime" id="link_lifetime" value="<?= htmlspecialcharsbx($linkLifetime) ?>" min="1">
            <?= Loc::getMessage('BASKETSHARE_OPTIONS_LINK_LIFETIME_DAYS') ?>
        </td>
    </tr>
    <tr>
        <td width="40%">
            <label for="basket_link"><?= Loc::getMessage('BASKETSHARE_OPTIONS_BASKET_LINK') ?>:</label>
        </td>
        <td width="60%">
            <input type="text" name="basket_link" id="basket_link" value="<?= htmlspecialcharsbx($basketLink) ?>">
        </td>
    </tr>

    <?php $tabControl->BeginNextTab(); ?>

    <?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php');
    ?>

    <?php $tabControl->Buttons(); ?>

    <input type="submit" name="Update" value="<?= Loc::getMessage('MAIN_SAVE') ?>" title="<?= Loc::getMessage('MAIN_OPT_SAVE_TITLE') ?>" class="adm-btn-save">
    <input type="reset" name="reset" value="<?= Loc::getMessage('MAIN_RESET') ?>">

    <?php $tabControl->End(); ?>
</form>