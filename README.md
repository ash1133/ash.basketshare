
**Код для вставки компонента "Поделиться корзиной"**

```
<?$APPLICATION->IncludeComponent(
	"ash:basket.share",
	".default",
	Array(
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"COMPONENT_TEMPLATE" => ".default",
		"INCLUDE_IN_BASKET" => "Y",
		"INCLUDE_IN_BASKET_SELECTOR" => ".basket-checkout-block-btn, .basket-items-list-header-filter",
		"POPUP_ID" => "basket-share-popup"
	)
);?>
```
