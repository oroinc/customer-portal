import $ from 'jquery';
import config from 'orofilter/js/filter/filter-settings';

export default $.extend(true, {}, config, {
    appearance: {
        'dropdown-mode': {
            criteriaClass: ' btn btn--default btn--size-s btn--full'
        },
        'toggle-mode': {
            criteriaClass: ' btn btn--plain btn--default-color btn--full'
        }
    }
});
