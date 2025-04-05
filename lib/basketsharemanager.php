<?php
namespace Ash\BasketShare;

use Ash\BasketShare\Traits\ShortLinkGeneratorTrait;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Fuser;

class BasketShareManager extends Base\BasketShareBase
{
    use ShortLinkGeneratorTrait;

    /**
     * Обработчик события OnPageStart для проверки перехода по короткой ссылке
     */
    public static function onPageStart()
    {
        $linkId = self::getIdByUri();
        if (
            $linkId
            && Loader::includeModule('sale')
            && Loader::includeModule(self::MODULE_ID)
        ) {
            self::applySharedBasket($linkId);
        }
    }

    /**
     * Добавление товаров из сохраненной корзины в корзину текущего пользователя
     */
    public static function applySharedBasket(int $linkId): bool
    {
        // Получаем данные сохраненной корзины
        $basketData = BasketShareHLBlock::getBasketByLinkId($linkId);


        if (!$basketData) {
            return false;
        }

        // Проверяем, не истек ли срок действия ссылки
        if (isset($basketData['UF_EXPIRE_DATE']) && !empty($basketData['UF_EXPIRE_DATE'])) {
            $expireDate = new \DateTime($basketData['UF_EXPIRE_DATE']);
            $currentDate = new \DateTime();

            if ($currentDate > $expireDate) {
                return false;
            }
        }

        // Получаем данные корзины
        $basketItems = json_decode($basketData['UF_BASKET_DATA'], true);

        if (!is_array($basketItems) || empty($basketItems)) {
            return false;
        }

        // Создаем событие перед применением корзины
        BasketShareEvent::onBeforeApplySharedBasket($linkId, $basketItems);

        if ($basketItems) {
            // Добавляем товары в корзину текущего пользователя
            $fuserId = Fuser::getId();
            $basket = Basket::loadItemsForFUser($fuserId, Context::getCurrent()->getSite());

            foreach ($basketItems as $item) {
                if (isset($item['PRODUCT_ID']) && $item['PRODUCT_ID'] > 0) {
                    $quantity = $item['QUANTITY'] ?? 1;
                    $props = $item['PROPS'] ?? [];

                    // Проверяем, есть ли уже такой товар в корзине
                    /**
                     * @var $existItem BasketItem
                     */
                    if ($existItem = self::GetExistsBasketItem($basket, $item['PRODUCT_ID']))
                        $existItem->setField('QUANTITY', $quantity);
                    else {
                        $basketItem = $basket->createItem('catalog', $item['PRODUCT_ID']);
                        $basketItem->setFields([
                            'QUANTITY' => $quantity,
                            'LID' => Context::getCurrent()->getSite(),
                            'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
                        ]);

                        if (!empty($props)) {
                            $basketPropertyCollection = $basketItem->getPropertyCollection();
                            $basketPropertyCollection->redefine($props);
                        }
                    }
                }
            }

            $basket->save();

            // Создаем событие после применения корзины
            BasketShareEvent::onAfterApplySharedBasket($linkId, $basketItems, $fuserId);

            return true;
        }

        return false;
    }

    /**
     * @param Basket $basket
     * @param int $productId
     * @param string $moduleId
     * @return BasketItem|bool
     */
    protected static function GetExistsBasketItem(
        BasketBase $basket,
        int $productId,
        string $moduleId = 'catalog'
    ): BasketItem | bool
    {
        $result = false;
        if(
            !empty($productId)
            && (intval($productId)>0)
            && (intval($productId)==$productId)
            && ($moduleId!='')
        ){
            foreach ($basket as $item) {
                if(
                    $productId == $item->getProductId()
                    && ($item->getField('MODULE') == $moduleId)
                ){
                    $result = $item;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Создание короткой ссылки для корзины
     */
    public static function createShareLink(): int | bool
    {
        if (
            !Loader::includeModule('sale')
            || !Loader::includeModule(self::MODULE_ID)
        ) {
            return false;
        }


        // Получаем текущую корзину
        $fuserId = Fuser::getId();
        $basket = Basket::loadItemsForFUser($fuserId, Context::getCurrent()->getSite());

        if ($basket->isEmpty())
            return false;

        // Формируем данные корзины для сохранения
        $basketItems = [];
        foreach ($basket->getBasketItems() as $basketItem) {
            $props = [];
            foreach ($basketItem->getPropertyCollection() as $property) {
                $props[] = [
                    'NAME' => $property->getField('NAME'),
                    'CODE' => $property->getField('CODE'),
                    'VALUE' => $property->getField('VALUE'),
                    'SORT' => $property->getField('SORT'),
                ];
            }

            $basketItems[] = [
                'PRODUCT_ID' => $basketItem->getProductId(),
                'QUANTITY' => $basketItem->getQuantity(),
                'CURRENCY' => $basketItem->getCurrency(),
                'PRICE' => $basketItem->getPrice(),
                'PROPS' => $props,
            ];
        }

        $linkId = BasketShareHLBlock::getLinkIdByBasket($basketItems);

        if (!$linkId) {

            // Создаем событие перед созданием ссылки
            BasketShareEvent::onBeforeCreateShareLink($basketItems, $fuserId);

            if (empty($basketItems))
                return false;

            // Получаем время жизни ссылки
            $linkLifetime = (int)Option::get(self::MODULE_ID, 'link_lifetime', 7);
            $expireDate = new \DateTime();
            $expireDate = \Bitrix\Main\Type\DateTime::createFromPhp($expireDate->modify("+{$linkLifetime} days"));

            // Создаем ссылку
            $linkId = self::generateUniqueLink(self::MODULE_ID);

            // Сохраняем данные в HL-блок
            $result = BasketShareHLBlock::addBasketShare([
                'UF_LINK_ID' => $linkId,
                'UF_FUSER_ID' => $fuserId,
                'UF_BASKET_DATA' => json_encode($basketItems),
                'UF_DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                'UF_EXPIRE_DATE' => $expireDate,
            ]);

            if (!$result) {
                return false;
            }

            // Создаем событие после создания ссылки
            BasketShareEvent::onAfterCreateShareLink($basketItems, $fuserId, $linkId, $expireDate);
        }

        return $linkId;
    }
}