import $ from 'jquery';

require('bootstrap-datepicker');

$(() => {
  $('.js-datepicker').datepicker({
    format: 'yyyy-mm-dd',
  });
});
