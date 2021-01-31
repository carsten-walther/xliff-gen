$(document).ready(function($) {

  // init select2
  $('select').select2({
    placeholder: 'Select an option'
  });

  // handle translation checkboxes
  $('#targetLanguages').change(function () {
    if ($(this).val().length > 0) {
      $('#translateTargetLanguages').attr('disabled', false);
    } else {
      $('#translateTargetLanguages').attr('disabled', true);
    }
  });

  // handle submit button on required fields
  $('#form').change(function () {
    let requiredFilled = true;
    if ($(this).find('#productName').val() === '') {
      requiredFilled = false;
    }
    if ($(this).find('#sourceLanguage').val() === '') {
      requiredFilled = false;
    }
    if ($(this).find('#files').val() === '') {
      requiredFilled = false;
    }
    if (requiredFilled) {
      $('#submit').attr('disabled', false);
    } else {
      $('#submit').attr('disabled', true);
    }
  });

});

$(window).resize(function () {

  // handle select2 on resize
  $('select').select2({
    placeholder: 'Select an option'
  });

});
