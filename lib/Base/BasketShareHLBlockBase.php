<?php

namespace Ash\BasketShare\Base;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\SystemException;
use CBXShortUri;
use CUserTypeEntity;

abstract class BasketShareHLBlockBase extends BasketShareBase
{
    /**
     * Создание HighloadBlock при установке модуля
     *
     * @return int|bool
     * @throws LoaderException
     * @throws SystemException
     */
    public static function create(): int | bool
    {
        if (!Loader::includeModule('highloadblock'))
            return false;

        $result = HighloadBlockTable::add([
            'NAME' => self::getHLBlockName(),
            'TABLE_NAME' => self::getHLBlockTableName(),
        ]);

        if($result->isSuccess()) {
            $hlblockId = $result->getId();
            self::createFields($hlblockId);

            return $hlblockId;
        }
        return false;
    }

    /**
     * Удаление HighloadBlock при удалении модуля
     *
     * @return bool
     */
    public static function delete(): bool
    {
        if (!Loader::includeModule('highloadblock') || !self::getHLBlockId()) {
            return false;
        }

        $entity = self::getEntity();

        if (!$entity)
            return false;

        $result = $entity::getList([
            'select' => ['ID', 'UF_LINK_ID'],
        ]);

        while ($record = $result->fetch()) {
            CBXShortUri::Delete($record['UF_LINK_ID']);
            $entity::delete($record['ID']);
        }

        $result = HighloadBlockTable::delete(self::getHLBlockId());

        return (bool)$result->isSuccess();
    }

    /**
     *  Получение объекта для работы с HL-блоком
     *
     * @return \Bitrix\Main\ORM\Data\DataManager|string|null
     */
    protected static function getEntity(): \Bitrix\Main\ORM\Data\DataManager|string|null
    {
        if (!Loader::includeModule('highloadblock')) {
            return null;
        }

        $hlblockId = Option::get(self::MODULE_ID, 'hlblock_id', 0);

        if ($hlblockId <= 0)
            return null;

        $hlblock = HighloadBlockTable::getById($hlblockId)->fetch();

        if (!$hlblock)
            return null;

        $entity = HighloadBlockTable::compileEntity($hlblock);

        return $entity->getDataClass();
    }

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

    /**
     * @return string
     */
    protected static function getHLBlockId(): string
    {
        return Option::get(self::MODULE_ID, 'hlblock_id');
    }

    /**
     * Создание полей для HighloadBlock
     *
     * @return void
     */
    protected static function createFields(int $hlblockId): void
    {
        $userTypeEntity = new CUserTypeEntity();

        $fields = [
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
                'FIELD_NAME' => 'UF_LINK_ID',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'UF_LINK_ID',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'S',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'Y',
                'SETTINGS' => ['DEFAULT_VALUE' => '', 'SIZE' => 20, 'ROWS' => 1, 'MIN_LENGTH' => 0, 'MAX_LENGTH' => 50, 'REGEXP' => ''],
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
                'FIELD_NAME' => 'UF_FUSER_ID',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'UF_FUSER_ID',
                'SORT' => 100,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'S',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'Y',
                'SETTINGS' => ['DEFAULT_VALUE' => '', 'SIZE' => 20, 'ROWS' => 1, 'MIN_LENGTH' => 0, 'MAX_LENGTH' => 50, 'REGEXP' => ''],
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
                'FIELD_NAME' => 'UF_BASKET_DATA',
                'USER_TYPE_ID' => 'string',
                'XML_ID' => 'UF_BASKET_DATA',
                'SORT' => 200,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => ['DEFAULT_VALUE' => '', 'SIZE' => '60', 'ROWS' => 5, 'MIN_LENGTH' => 0, 'MAX_LENGTH' => 0, 'REGEXP' => ''],
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
                'FIELD_NAME' => 'UF_DATE_CREATE',
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => 'UF_DATE_CREATE',
                'SORT' => 300,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'Y',
                'SHOW_FILTER' => 'S',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => ['DEFAULT_VALUE' => ['TYPE' => 'NOW', 'VALUE' => '']],
            ],
            [
                'ENTITY_ID' => 'HLBLOCK_' . $hlblockId,
                'FIELD_NAME' => 'UF_EXPIRE_DATE',
                'USER_TYPE_ID' => 'datetime',
                'XML_ID' => 'UF_EXPIRE_DATE',
                'SORT' => 400,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'S',
                'SHOW_IN_LIST' => 'Y',
                'EDIT_IN_LIST' => 'Y',
                'IS_SEARCHABLE' => 'N',
                'SETTINGS' => ['DEFAULT_VALUE' => ['TYPE' => 'NONE', 'VALUE' => '']],
            ],
        ];

        foreach ($fields as $field) {
            $userTypeEntity->Add($field);
        }
    }
}
