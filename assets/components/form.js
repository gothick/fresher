import 'bootstrap-datepicker/dist/css/bootstrap-datepicker.css';

import $ from 'jquery';

require('bootstrap-datepicker');

$(() => {
  $('.js-datepicker').datepicker({
    format: 'yyyy-mm-dd',
    todayHighlight: true
  });
});
