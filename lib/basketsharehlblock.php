<?php
namespace Ash\BasketShare;

use Ash\BasketShare\Traits\ShortLinkGeneratorTrait;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Sale\Fuser;
use CBXShortUri;
use CUserTypeEntity;

class BasketShareHLBlock extends Base\BasketShareBase
{
    use ShortLinkGeneratorTrait;

    /**
     * Получение объекта для работы с HL-блоком
     */
    protected static function getHlblockEntity()
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
     * Создание HighloadBlock при установке модуля
     *
     * @return int|bool
     */
    public static function createHLBlock(): int | bool
    {
        if (!Loader::includeModule('highloadblock')) {
            return false;
        }

        $result = HighloadBlockTable::add([
            'NAME' => self::getHLBlockName(),
            'TABLE_NAME' => self::getHLBlockTableName(),
        ]);
        if($result->isSuccess()) {
            $hlblockId = $result->getId();
            self::createHLBlockFields($hlblockId);

            return $hlblockId;
        }
        return false;
    }

    /**
     * Создание полей для HighloadBlock
     *
     * @return void
     */
    private static function createHLBlockFields(int $hlblockId): void
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

    /**
     * Удаление HighloadBlock при удалении модуля
     *
     * @return bool
     */
    public static function deleteHLBlock(): bool
    {
        if (!Loader::includeModule('highloadblock') || !self::getHLBlockId()) {
            return false;
        }

        $entity = self::getHlblockEntity();

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
     *  Добавление записи о корзине
     *
     * @param array $data
     * @return int|bool
     * @throws \Exception
     */
    public static function addBasketShare(array $data): int | bool
    {
        $entity = self::getHlblockEntity();

        if (!$entity)
            return false;

        $result = $entity::add($data);

        return $result->isSuccess() ? $result->getId() : false;
    }

    /**
     *  Получение корзины по id ссылки
     *
     * @param int $linkId
     * @return array|bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getBasketByLinkId(int $linkId) : array | bool
    {
        $entity = self::getHlblockEntity();

        if (!$entity) {
            return false;
        }

        $result = $entity::getList([
            'filter' => ['=UF_LINK_ID' => $linkId],
            'select' => ['*'],
            'limit' => 1,
        ]);

        return $result->fetch();
    }

    /**
     *  Проверка наличия ссылки по корзине
     *
     * @param array $basket
     * @return int|bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getLinkIdByBasket(array $basket) : int | bool
    {
        $entity = self::getHlblockEntity();
        $fuserId = Fuser::getId();

        if (!$entity) {
            return false;
        }

        $result = $entity::getList([
            'filter' => [
                '=UF_BASKET_DATA' => json_encode($basket),
                '=UF_FUSER_ID' => $fuserId,
            ],
            'select' => ['*'],
            'limit' => 1,
        ])->fetch();

        return $result['UF_LINK_ID'] ?: false;
    }

    /**
     *  Удаление устаревших записей
     *
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function cleanupExpiredRecords(): bool
    {
        $entity = self::getHlblockEntity();

        if (!$entity) {
            return false;
        }

        $currentDate = new \Bitrix\Main\Type\DateTime();

        $result = $entity::getList([
            'filter' => [
                '<=UF_EXPIRE_DATE' => $currentDate,
            ],
            'select' => ['ID','UF_LINK_ID'],
        ]);

        while ($record = $result->fetch()) {
            self::deleteShortLink($record['UF_LINK_ID']);
            $entity::delete($record['ID']);
        }

        return true;
    }

    /**
     *  Обработка события OnBeforeDelete
     *
     * @param \Bitrix\Main\Entity\Event $event
     * @return \Bitrix\Main\Entity\EventResult
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function onBeforeDelete(\Bitrix\Main\Entity\Event $event)
    {
        $id = $event->getParameter("id")['ID'];
        $entity = self::getHlblockEntity();

        $result = $entity::getById($id)->fetch();
        self::deleteShortLink($result['UF_LINK_ID']);

        return new \Bitrix\Main\Entity\EventResult();
    }
}
