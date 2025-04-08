<?php

namespace Ash\BasketShare;

use Ash\BasketShare\Interfaces\BasketSharedHLBlockExpiredInterface;
use Ash\BasketShare\Interfaces\BasketSharedHLBlockHandleableInterface;
use Ash\BasketShare\Traits\BasketSharedHLBlockExpiredTrait;
use Ash\BasketShare\Traits\ShortLinkGeneratorTrait;
use Bitrix\Sale\Fuser;
use CUserTypeEntity;

class BasketShareHLBlock extends Base\BasketShareHLBlockBase implements
    BasketSharedHLBlockExpiredInterface,
    BasketSharedHLBlockHandleableInterface
{
    use ShortLinkGeneratorTrait, BasketSharedHLBlockExpiredTrait;

    /**
     *  Добавление записи о корзине
     *
     * @param array $data
     * @return int|bool
     * @throws \Exception
     */
    public static function addBasketShare(array $data): int | bool
    {
        $entity = self::getEntity();

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
        $entity = self::getEntity();

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
        $entity = self::getEntity();
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
     *  Обработка события OnBeforeDelete
     *
     * @param \Bitrix\Main\Entity\Event $event
     * @return \Bitrix\Main\Entity\EventResult
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function onBeforeDelete(\Bitrix\Main\Entity\Event $event): \Bitrix\Main\Entity\EventResult
    {
        $id = $event->getParameter("id")['ID'];
        $entity = self::getEntity();

        $result = $entity::getById($id)->fetch();
        self::deleteShortLink($result['UF_LINK_ID']);

        return new \Bitrix\Main\Entity\EventResult();
    }
}
