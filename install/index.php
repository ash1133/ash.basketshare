<?php
//require_once __DIR__ . '/../lib/BasketShareHLBlock.php';

use Ash\BasketShare\BasketShareHLBlock;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class ash_basketshare extends CModule
{
    public $MODULE_ID = 'ash.basketshare';
    public $MODULE_GROUP_RIGHTS = 'Y';
    public $MODULE_NAME;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_DESCRIPTION;
    protected string $HLBlockName = 'BasketShare';
    protected string $HLBlockTableName = 'basket_share';
    protected array $eventHandlers = [];

    public function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . '/version.php');

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = Loc::getMessage('BASKETSHARE_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('BASKETSHARE_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('BASKETSHARE_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('BASKETSHARE_PARTNER_URI');

        $this->eventHandlers = [
            ['main', 'OnPageStart', 'ash.basketshare', '\ash\BasketShare\BasketShareManager', 'onPageStart', 10],
            ['', $this->HLBlockName."OnBeforeDelete", 'ash.basketshare', '\ash\BasketShare\BasketShareHLBlock', 'onBeforeDelete'],
        ];
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if (!ModuleManager::isModuleInstalled('highloadblock')) {
            $APPLICATION->ThrowException(Loc::getMessage('BASKETSHARE_NEED_MODULES', ['#MODULE#' => 'highloadblock']));
            return false;
        }

        if (!ModuleManager::isModuleInstalled('sale')) {
            $APPLICATION->ThrowException(Loc::getMessage('BASKETSHARE_NEED_MODULES', ['#MODULE#' => 'sale']));
            return false;
        }

        ModuleManager::registerModule($this->MODULE_ID);

        $this->InstallFiles();
        $this->InstallDB();
        $this->InstallEvents();

        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION, $step;

        $step = intval($step);

        if($step<2)
            $APPLICATION->IncludeAdminFile(
                "Удаление модуля {$this->MODULE_NAME}",
                __DIR__."/unstep1.php"
            );
        elseif($step==2) {
            $context = Application::getInstance()->getContext();
            $request = $context->getRequest();

            $this->UnInstallEvents();
            $this->UnInstallFiles();

            if (
                $request['save_data'] !== 'Y'
                && Loader::includeModule($this->MODULE_ID)
            ) {
                $this->UnInstallDB();
            }

            ModuleManager::unRegisterModule($this->MODULE_ID);
        }

        return true;
    }

    public function InstallFiles()
    {
        CopyDirFiles(
            __DIR__ . '/components',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components',
            true, true
        );

        return true;
    }

    public function UnInstallFiles()
    {
        DeleteDirFilesEx('/bitrix/components/ash');
        return true;
    }

    public function InstallDB()
    {
        Option::set($this->MODULE_ID, 'hlblock_name', $this->HLBlockName);
        Option::set($this->MODULE_ID, 'hlblock_table_name', $this->HLBlockTableName);

        if (Loader::includeModule($this->MODULE_ID)) {
            $result = BasketShareHLBlock::create();

            if ($result)
                Option::set($this->MODULE_ID, 'hlblock_id', $result);
        }

        // Установка опций модуля
        Option::set($this->MODULE_ID, 'link_lifetime', '7');
        Option::set($this->MODULE_ID, 'basket_link', '/personal/cart/');

        return true;
    }

    public function UnInstallDB()
    {
        BasketShareHLBlock::delete();

        Option::delete($this->MODULE_ID);

        return true;
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        foreach ($this->eventHandlers as $handler) {
            $eventManager->registerEventHandler(
                $handler[0],
                $handler[1],
                $handler[2],
                $handler[3],
                $handler[4],
                $handler[5] ?: 10
            );
        }

        return true;
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();

        foreach ($this->eventHandlers as $handler) {
            $eventManager->unRegisterEventHandler(
                $handler[0],
                $handler[1],
                $handler[2],
                $handler[3],
                $handler[4]
            );
        }

        return true;
    }
}