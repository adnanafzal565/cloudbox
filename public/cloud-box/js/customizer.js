(function (jQuery) {
    "use strict";
    // data-mode="click" for using event
    // data-dark="false" for property
    
})(jQuery)

/*------Select Dropdown--------*/
$(function() {
  
    $('.select-dropdown > .selet-caption').on('click', function() {
      $(this).parent().toggleClass('open');
    });
    
    $('.select-dropdown > .list-item > .item').on('click', function() {
      $('.select-dropdown > .list-item > .item').removeClass('selected');
      $(this).addClass('selected').parent().parent().removeClass('open').children('.selet-caption').text( $(this).text() );
    });
    
    $(document).on('keyup', function(evt) {
      if ( (evt.keyCode || evt.which) === 27 ) {
        $('.select-dropdown').removeClass('open');
      }
    });
    
    $(document).on('click', function(evt) {
      if ( $(evt.target).closest(".select-dropdown > .selet-caption").length === 0 ) {
        $('.select-dropdown').removeClass('open');
      }
    });
    
  });