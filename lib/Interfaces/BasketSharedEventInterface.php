<?php

namespace Ash\BasketShare\Interfaces;

interface BasketSharedEventInterface
{
    public static function onBeforeCreateShareLink(&$basketItems, $fuserId) : void;
    public static function onAfterCreateShareLink($basketItems, $fuserId, $shareLinkId, $expireDate): void;
    public static function onBeforeApplySharedBasket($linkId, &$basketItems): void;
    public static function onAfterApplySharedBasket($linkId, $basketItems, $fuserId): void;
}