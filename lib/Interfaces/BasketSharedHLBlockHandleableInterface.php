<?php

namespace Ash\BasketShare\Interfaces;

interface BasketSharedHLBlockHandleableInterface
{
    public static function onBeforeDelete(\Bitrix\Main\Entity\Event $event): \Bitrix\Main\Entity\EventResult;
}