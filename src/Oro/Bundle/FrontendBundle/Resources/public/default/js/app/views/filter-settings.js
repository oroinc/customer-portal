import $ from 'jquery';
import config from 'orofilter/js/filter/filter-settings';
import viewportManager from 'oroui/js/viewport-manager';

export default $.extend(true, {}, config, {
    fullScreenViewport: 'tablet',
    isFullScreen() {
        return viewportManager.isApplicable(this.fullScreenViewport);
    },
    appearance: {
        'dropdown-mode': {
            criteriaClass: ' btn btn--default btn--size-s btn--full'
        },
        'toggle-mode': {
            criteriaClass: ' btn btn--plain btn--default-color btn--full'
        }
    }
});
