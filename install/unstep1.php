<form action="<?= $APPLICATION->GetCurPage()?>">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID?>">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="id" value="ash.basketshare">
    <input type="hidden" name="step" value="2">
    <?CAdminMessage::ShowMessage(GetMessage("MOD_UNINST_WARN"))?>
    <p><?= GetMessage("MOD_UNINST_SAVE")?></p>
    <p><input type="checkbox" name="save_data" id="save_data" value="Y" checked><label for="save_data"><?echo GetMessage("MOD_UNINST_SAVE_TABLES")?></label></p>
    <input type="submit" name="inst" value="<?= GetMessage("MOD_UNINST_DEL")?>">
</form>