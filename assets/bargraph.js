const $ = require('jquery');

$(() => {
  $('.bargraph').each(function setBar() {
    const progress = 100.0 * parseFloat($(this).data('progress'));
    const bar = $(this).children('.bar').eq(0);
    bar.width(`${progress}%`);
    if (progress > 70) {
      bar.addClass('warm');
    }
    if (progress > 90) {
      bar.addClass('hot');
    }
  });
});
