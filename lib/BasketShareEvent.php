<?php

namespace Ash\BasketShare;

use Ash\BasketShare\Interfaces\BasketSharedEventInterface;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class BasketShareEvent extends Base\BasketShareBase implements BasketSharedEventInterface
{
    /**
     * Вызов события перед созданием ссылки
     */
    public static function onBeforeCreateShareLink(&$basketItems, $fuserId) : void
    {
        $event = new Event(self::MODULE_ID, 'OnBeforeCreateShareLink', [
            'basket_items' => $basketItems,
            'fuser_id' => $fuserId,
        ]);

        $event->send();

        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() === EventResult::ERROR) {
                    $basketItems = [];
                }
                if ($eventResult->getType() === EventResult::SUCCESS) {
                    $params = $eventResult->getParameters();
                    if (isset($params['basket_items']) && is_array($params['basket_items'])) {
                        $basketItems = $params['basket_items'];
                    }
                }
            }
        }

    }

    /**
     * Вызов события после создания ссылки
     */
    public static function onAfterCreateShareLink($basketItems, $fuserId, $shareLinkId, $expireDate): void
    {
        $event = new Event(self::MODULE_ID, 'OnAfterCreateShareLink', [
            'basket_items' => $basketItems,
            'fuser_id' => $fuserId,
            'share_link_id' => $shareLinkId,
            'expire_date' => $expireDate,
        ]);

        $event->send();
    }

    /**
     * Вызов события перед применением корзины
     */
    public static function onBeforeApplySharedBasket($linkId, &$basketItems): void
    {
        $event = new Event(self::MODULE_ID, 'OnBeforeApplySharedBasket', [
            'link_id' => $linkId,
            'basket_items' => $basketItems,
        ]);

        $event->send();

        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() === EventResult::ERROR) {
                    $basketItems = [];
                } else {
                    $params = $eventResult->getParameters();
                    if (isset($params['basket_items']) && is_array($params['basket_items'])) {
                        $basketItems = $params['basket_items'];
                    }
                }
            }
        }
    }

    /**
     * Вызов события после применения корзины
     */
    public static function onAfterApplySharedBasket($linkId, $basketItems, $fuserId): void
    {
        $event = new Event(self::MODULE_ID, 'OnAfterApplySharedBasket', [
            'link_id' => $linkId,
            'basket_items' => $basketItems,
            'fuser_id' => $fuserId,
        ]);

        $event->send();
    }
}
