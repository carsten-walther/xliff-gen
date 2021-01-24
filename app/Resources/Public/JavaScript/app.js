$(document).ready(function($) {
  $('select').select2({
    placeholder: 'Select an option'
  });
});

$(window).resize(function () {
  $('select').select2({
    placeholder: 'Select an option'
  });
});
