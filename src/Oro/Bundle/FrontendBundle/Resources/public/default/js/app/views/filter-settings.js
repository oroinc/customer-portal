import $ from 'jquery';
import config from 'orofilter/js/filter/filter-settings';
import viewportManager from 'oroui/js/viewport-manager';
import moduleConfig from 'module-config';

const defaults = {
    fullScreenViewport: 'tablet',
    minHeightBreakpoint: null
};

const settings = $.extend(true, {}, config, defaults, moduleConfig(module.id), {
    isFullScreen() {
        if (viewportManager.isApplicable(this.fullScreenViewport)) {
            return true;
        }

        if (this.minHeightBreakpoint) {
            return window.innerHeight <= this.minHeightBreakpoint;
        }

        return false;
    },
    appearance: {
        'dropdown-mode': {
            criteriaClass: ' select select--full'
        }
    }
});

export default settings;
