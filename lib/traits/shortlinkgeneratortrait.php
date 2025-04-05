<?php
namespace Ash\BasketShare\Traits;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use CBXShortUri;

trait ShortLinkGeneratorTrait
{
    /**
     * Генерирует уникальную короткую ссылку.
     *
     * @param string $moduleId ID модуля (для получения настроек)
     * @param string $optionName Код опции с базовым URI (например, 'basket_link')
     * @param string $defaultUri URI по умолчанию, если опция не задана
     * @return int|false
     */
    protected static function generateUniqueLink(
        string $moduleId,
        string $optionName = 'basket_link',
        string $defaultUri = '/personal/cart/'
    ): int | bool
    {
        $arFields = [
            "URI" => Option::get($moduleId, $optionName, $defaultUri),
            "SHORT_URI" => CBXShortUri::GenerateShortUri(),
            "STATUS" => 301, // Редирект (301 или 302)
        ];

        return CBXShortUri::Add($arFields);
    }

    /**
     * @param int $linkId ID короткой ссылки
     * @return string|bool
     */
    public static function getShortLink(int $linkId): string | bool
    {
        $result = CBXShortUri::GetList([],['ID' => $linkId])->fetch();

        if (!$result) return false;

        $request = \Bitrix\Main\Context::getCurrent()->getRequest();
        $proto = $request->isHttps() ? "https://" : "http://";
        $host = $request->getHttpHost();

        return $proto . implode('/', [$host, $result['SHORT_URI']]);
    }

    /**
     * @param int $linkId ID короткой ссылки
     * @return void
     */
    protected static function deleteShortLink(int $linkId): void
    {
        CBXShortUri::Delete($linkId);
    }

    /**
     * @return int|bool
     */
    protected static function getIdByUri(): int | bool
    {
        $request = Context::getCurrent()->getRequest();

        return ($link = CBXShortUri::GetUri($request->getRequestedPage()))
            ? $link['ID']
            : false;
    }
}