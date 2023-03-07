class CardFormCustomization {
    init() {
        this.handleButtonClickEvent()
        this.handleColorInputChanges()
        this.showOmiseCardForm()
    }

    showModal() {
        const element = document.getElementById('omise-modal')
        element.style.display = 'flex'
        this.setDesignFormValues()
    }

    showPreviewModal() {
        const element = document.getElementById('omise-preview-modal')
        element.style.display = 'flex'
    }

    hidePreviewModal() {
        const element = document.getElementById('omise-preview-modal')
        element.style.display = 'none'
    }

    hideModal() {
        const element = document.getElementById('omise-modal')
        element.style.display = 'none'
    }

    getSelectedTheme() {
        const cardThemeInputName = `groups\\[omise\\]\\[groups\\]\\[omise_cc\\]\\[fields\\]\\[card_form_theme\\]\\[value\\]`
        return this.getInputValue(cardThemeInputName)
    }

    getInitialDesign() {
        const design = this.parseJson(OMISE_CARD_CUSTOMIZATION_DESIGN)
        const darkTheme = this.parseJson(OMISE_CARD_CUSTOMIZATION_DARK_THEME)
        const lightTheme = this.parseJson(OMISE_CARD_CUSTOMIZATION_LIGHT_THEME)
        if (!design) {
            return this.getSelectedTheme() == 'dark' ? darkTheme : lightTheme
        }
        return design
    }

    setInputValue(name, val) {
        document.querySelector(`[name=${name}]`).value = val
    }

    getInputValue(name) {
        const element = document.querySelector(`[name=${name}]`);
        if(element) {
            return element.value
        }
        return null;
    }

    setColorInputValue(element) {
        const valueElement = element.nextElementSibling
        if (!valueElement) {
            element.insertAdjacentHTML('afterend', `<span>${element.value}</span>`)
        } else {
            valueElement.innerHTML = element.value
        }
    }

    handleColorInputChanges() {
        const colorInputs = document.querySelectorAll('.omise-color-input-container')

        colorInputs.forEach((element) => {
            const input = element.querySelector('.omise-color-input')

            this.setColorInputValue(input)
            input.addEventListener('change', (event) => {
                this.setColorInputValue(event.target)
            })

            element.addEventListener('click', (event) => {
                let input = event.target.querySelector('.omise-color-input')
                if (!input) {
                    input = event.target.previousElementSibling
                }
                const clickEvent = new MouseEvent('click')
                if(input) {
                    input.dispatchEvent(clickEvent)
                }
            })
        })
    }

    setDesignFormValues() {
        const design = this.getInitialDesign()
        const self = this
        if (design) {
            Object.keys(design).forEach(function (componentKey) {
                const componentValues = design[componentKey]
                Object.keys(componentValues).forEach(function (key) {
                    self.setInputValue(`${componentKey}\\[${key}\\]`, componentValues[key])
                })
            })
        }
    }

    getDesignFormValues() {
        const design = this.getInitialDesign()
        let formValues = { ...design }
        const self = this
        if (design) {
            Object.keys(design).forEach(function (componentKey) {
                const componentValues = design[componentKey]
                Object.keys(componentValues).forEach(function (key) {
                    const val = self.getInputValue(`${componentKey}\\[${key}\\]`)
                    formValues[componentKey][key] = val
                })
            })
        }
        return formValues
    }

    submitForm(design) {
        const element = document.getElementById(OMISE_CARD_CUSTOMIZATION_INPUT_ID)
        element.value = design? JSON.stringify(design) : null;
        const form = document.getElementById('config-edit-form')
        form.submit()
        this.hideModal()
    }

    getOmisePublicKey() {
        const testPublicKey = this.getInputValue(`groups\\[omise\\]\\[fields\\]\\[test_public_key\\]\\[value\\]`)
        const livePublicKey = this.getInputValue(`groups\\[omise\\]\\[fields\\]\\[live_public_key\\]\\[value\\]`)
        const sandbox = this.getInputValue(`groups\\[omise\\]\\[fields\\]\\[sandbox_status\\]\\[value\\]`)
        return sandbox ? testPublicKey: livePublicKey
    }

    showOmiseCardForm() {
        this.getOmisePublicKey();
        const element = document.getElementById('omise-card')
        const { input, checkbox, font } = this.getDesignFormValues();
        OmiseCard.configure({
            publicKey: this.getOmisePublicKey(),
            element,
            locale: 'en',
            customCardForm: true,
            customCardFormTheme: this.getSelectedTheme(),
            style: {
                fontFamily: font.name,
                fontSize: font.size,
                input: {
                    height: input.height,
                    borderRadius: input.border_radius,
                    border: `1.2px solid ${input.border_color}`,
                    focusBorder: `1.2px solid ${input.active_border_color}`,
                    background: input.background_color,
                    color: input.text_color,
                    labelColor: input.label_color,
                    placeholderColor: input.placeholder_color,
                },
                checkBox: {
                    textColor: checkbox.text_color,
                    themeColor: checkbox.theme_color,
                    border: `1.2px solid ${input.border_color}`,
                }
            }
        })
        OmiseCard.open({});
    }

    handleButtonClickEvent() {
        document.getElementById('omise-card-form-customization-submit').addEventListener('click', (event) => {
            event.preventDefault()
            this.submitForm(this.getDesignFormValues())
        })

        document.getElementById('omise-card-form-customization-reset').addEventListener('click', (event) => {
            event.preventDefault()
            this.submitForm(null)
        })

        document.getElementById('omise-card-form-customization-preview').addEventListener('click', (event) => {
            event.preventDefault()
            this.showPreviewModal();
            this.showOmiseCardForm()
        })

        document.getElementById('omise-card-form-customization-close-preview').addEventListener('click', (event) => {
            event.preventDefault()
            this.hidePreviewModal()
        })
        

        document.getElementById('omise-card-form-customization-cancel').addEventListener('click', (event) => {
            event.preventDefault()
            this.hideModal()
        })
    }

    parseJson(data) {
        try {
            return JSON.parse(data)
        }
        catch (error) {
            return null
        }
    }
}

const cardFormCustomization = new CardFormCustomization()
cardFormCustomization.init()
