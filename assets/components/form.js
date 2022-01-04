require('bootstrap-datepicker');

import $ from 'jquery';

$(function() {
    $('.js-datepicker').datepicker({
        format: 'yyyy-mm-dd'
    });
});
