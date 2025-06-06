class BasketShare {
    constructor(params) {
        this.componentPath = params.componentPath || ''
        this.popupId = params.popupId || 'basket-share-popup-popup'
        this.buttonSelector = params.buttonSelector || 'basket-share-popup-button'
        this.buttonContainerSelector = params.buttonContainerSelector || 'basket-share-popup-button-container'
        this.linkInputId = params.linkInputId || 'basket-share-popup-link'
        this.copyButtonId = params.copyButtonId || 'basket-share-popup-copy'
        this.closeButtonId = params.closeButtonId || 'basket-share-popup-close'
        this.loadingId = params.loadingId || 'basket-share-popup-loading'
        this.errorId = params.errorId || 'basket-share-popup-error'
        this.successId = params.successId || 'basket-share-popup-copy-success'
        this.signedParameters = params.signedParameters || ''
        this.moveButtonToBasket = params.moveButtonToBasket || false
        this.moveButtonPlaceSelector = params.moveButtonPlaceSelector || false

        this.node = {
            popup: document.getElementById(this.popupId),
            button: document.querySelector(`[data-entity=${this.buttonSelector}]`),
            buttonContainer: document.querySelectorAll(`[data-entity=${this.buttonContainerSelector}]`),
            linkInput: document.getElementById(this.linkInputId),
            copyButton: document.getElementById(this.copyButtonId),
            closeButton: document.getElementById(this.closeButtonId),
            loading: document.getElementById(this.loadingId),
            error: document.getElementById(this.errorId),
            success: document.getElementById(this.successId),
        }

        this.popup = null

        this.init()
    }

    init() {
        if (this.moveButtonToBasket) {
            for (const el of document.querySelectorAll(this.moveButtonPlaceSelector)) {
                const newBtn = this.node.button.cloneNode(true);
                el.append(newBtn)
            }
            this.node.button.remove()
            this.node.button = []
            for (const btn of document.querySelectorAll(`[data-entity=${this.buttonSelector}]`))
                this.node.button.push(btn)
        }

        this.initPopup()
        this.bindEvents()
    }

    initPopup() {
        if (typeof BX.PopupWindowManager !== 'undefined') {
            this.popup = BX.PopupWindowManager.create(
                this.popupId,
                null,
                {
                    content: this.node.popup,
                    closeIcon: true,
                    closeByEsc: true,
                    overlay: {
                        opacity: 50
                    },
                    events: {
                        onPopupShow: this.onPopupShow.bind(this)
                    }
                }
            );
        }
    }

    bindEvents() {
        if (this.moveButtonToBasket)
            for (const el of this.node.button)
                el.addEventListener('click', this.onButtonClick.bind(this))
        else
            this.node.button
                .addEventListener('click', this.onButtonClick.bind(this))


        this.node.copyButton
            .addEventListener('click', this.onCopyClick.bind(this))

        if (this.node.closeButton)
            this.node.closeButton
                .addEventListener('click', this.onCloseClick.bind(this))

        if (this.popup && this.popup.overlay)
            this.popup.overlay.element
                .addEventListener('click', this.onCloseClick.bind(this))
    }

    onButtonClick(event) {
        event.preventDefault()

        if (this.popup)
            this.popup.show()
    }

    onPopupShow() {
        this.node.linkInput.value = ''
        this.node.error.style.display = 'none'
        this.node.success.style.display = 'none'
        this.node.loading.style.display = 'block'

        // Генерируем ссылку
        this.generateLink()
    }

    generateLink() {
        this.node.loading.style.display = 'block';

        BX.ajax.runComponentAction('ash:basket.share', 'generateLink', {
            mode: 'class',
            signedParameters: this.signedParameters
        })
            .then(
                response => {
                    this.node.loading.style.display = 'none';

                    if (response.data.success) {
                        this.node.linkInput.value = response.data.link;
                    } else {
                        this.showError(response.data.error || 'Error generating link');
                    }
                },
                response => {
                    this.node.loading.style.display = 'none';
                    this.showError(response.errors[0].message || 'Error generating link');
                }
            );
    }


    onCopyClick(event) {
        event.preventDefault()

        const linkInput = this.node.linkInput

        if (linkInput.value) {
            linkInput.select()
            document.execCommand('copy');

            this.node.success.style.display = 'block';

            setTimeout(()=> {
                this.node.success.style.display = 'none';
            }, 3000);
        }
    }

    onCloseClick(event) {
        event.preventDefault();

        if (this.popup) {
            this.popup.close();
        }
    }

    showError(errorMessage) {
        this.node.error.innerHTML = errorMessage;
        this.node.error.style.display = 'block';
    }
}
