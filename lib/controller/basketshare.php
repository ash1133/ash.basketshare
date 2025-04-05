<?php
namespace Ash\BasketShare\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Ash\BasketShare\BasketShareManager;

class BasketShare extends Controller
{
    /**
     * Конфигурация фильтров действий
     */
    protected function getDefaultPreFilters()
    {
        return [
            new ActionFilter\HttpMethod(
                [ActionFilter\HttpMethod::METHOD_POST]
            ),
            new ActionFilter\Csrf(),
        ];
    }

    /**
     * Действие для генерации ссылки на корзину
     */
    public function generateLinkAction()
    {
        $shareLinkId = BasketShareManager::createShareLink();

        if (!$shareLinkId) {
            $this->addError(new Error('Failed to create share link'));
            return null;
        }

        return [
            'link' => $shareLinkId,
        ];
    }
}
