import ThemeConfigurationPreviewView from 'orotheme/js/app/views/theme-configuration-preview-view';

const ThemeConfigurationPreviewQuickAccessButtonView = ThemeConfigurationPreviewView.extend({
    /**
     * @inheritdoc
     */
    onChangeFieldsSelector: '.quick-access-button-type',
    /**
     * @inheritdoc
     */
    constructor: function ThemeConfigurationPreviewQuickAccessButtonView(options) {
        ThemeConfigurationPreviewQuickAccessButtonView.__super__.constructor.call(this, options);
    },

    /**
     * @inheritDoc
     * @param {HTMLElement} el
     * @returns {string}
     */
    getSource(el) {
        if (!el) {
            return '';
        }

        return this.getSourceOfElement(el);
    },

    /**
     * @inheritDoc
     * @param {HTMLElement} el
     * @returns {string}
     */
    getSourceOfElement(el) {
        const previewEl = this.el.querySelector('[data-preview-key]');

        if (!previewEl) {
            return this.getDefaultPreview();
        }

        let value = el.value ?? '';

        if (value.length) {
            value = value[0].toUpperCase() + value.slice(1);
            const preview = previewEl.dataset?.[`preview${value}`];

            if (preview) {
                return preview;
            }
        }

        return this.getDefaultPreview();
    }
});

export default ThemeConfigurationPreviewQuickAccessButtonView;
