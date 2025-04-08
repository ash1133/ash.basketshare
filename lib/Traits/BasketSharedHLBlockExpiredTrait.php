<?php

namespace Ash\BasketShare\Traits;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use CBXShortUri;

trait BasketSharedHLBlockExpiredTrait
{
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
        $entity = self::getEntity();

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
}