<?php
namespace Ash\BasketShare\Base;

use Bitrix\Main\Config\Option;

abstract class BasketShareBase
{
    const MODULE_ID = 'ash.basketshare';

    /**
     * @return string
     */
    protected static function getHLBlockName(): string
    {
        return Option::get(self::MODULE_ID, 'hlblock_name');
    }

    /**
     * @return string
     */
    protected static function getHLBlockTableName(): string
    {
        return Option::get(self::MODULE_ID, 'hlblock_table_name');
    }

    protected static function getHLBlockId(): string
    {
        return Option::get(self::MODULE_ID, 'hlblock_id');
    }
}
