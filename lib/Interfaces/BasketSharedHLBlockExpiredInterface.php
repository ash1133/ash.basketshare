<?php

namespace Ash\BasketShare\Interfaces;

interface BasketSharedHLBlockExpiredInterface
{
    public static function cleanupExpiredRecords(): bool;
}