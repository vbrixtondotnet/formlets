
(function () {

  /*==============================================================
   Mobile Menu
   =============================================================*/

   $('#mobile-menu').click(function(){
    $('.navbar-links').toggle();
   });

   $(window).resize(function(){
     if ($(window).width() > 991){
       $('.navbar-links').css("display", "inline-block");
     }
     else{
       $('.navbar-links').css("display", "none")
     }
   });

    /*==============================================================
     Grid List View
     =============================================================*/

     function setHeight() {
       var actionHeight = $('.grid-view ul li ul.form-details li.form-actions').height() + 20;
       var formdetailsHeight = $('.grid-view ul li ul.form-details').height() + 20;

       if(actionHeight > formdetailsHeight){
         $('.grid-view ul li ul.form-details').css('height', actionHeight);
       }
       else if(actionHeight < formdetailsHeight){
         // $('.grid-view ul li ul.form-details').css('height', 'auto');
         // $('.grid-view ul li ul.form-details li.form-actions').css('height', '100%');
       }
     }

    if ($(window).width() < 992) {
        $('#gridlistview').removeClass('list-view');
        $('#listview-icon').removeClass('active');
        $('#listview-icon').addClass('disabled');
        $('#gridlistview').addClass('grid-view');
        $('#gridview-icon').addClass('active');
     setHeight();
    }
    else {
     $('#gridlistview').addClass('list-view');
     $('#listview-icon').addClass('active');
    }

    $(window).resize(function() {
      setHeight();

      if ($(window).width() < 992) {
         $('#gridlistview').removeClass('list-view');
         $('#listview-icon').removeClass('active');
         $('#listview-icon').addClass('disabled');
         $('#gridlistview').addClass('grid-view');
         $('#gridview-icon').addClass('active');
      }
      else {
        $('#listview-icon').removeClass('disabled');
      }
    });

    $('#gridview-icon').click(function(){
      $('#gridlistview').removeClass('list-view');
      $('#gridlistview').addClass('grid-view');
      $('#listview-icon').removeClass('active');
      $('#gridview-icon').addClass('active');
      setHeight();
    });

    $('#listview-icon').click(function(){
      $('#gridlistview').removeClass('grid-view');
      $('#gridlistview').addClass('list-view');
      $('#gridview-icon').removeClass('active');
      $('#listview-icon').addClass('active');
      $('.list-view ul li ul.form-details').css('height', 'auto');
    });

    /*==============================================================
     Account - Company Details
     =============================================================*/
     $('#companyCheck').click(function() {
        if( $(this).is(':checked')) {
            $("#CompanyDetails").show();
        } else {
            $("#CompanyDetails").hide();
        }
    });


    /*==============================================================
     Back To Top
     =============================================================*/


    $('.scrollup').on('click', function () {
        $("html, body").animate({
            scrollTop: 0
        }, 600);
        return false;
    });

    $(window).on("scroll", function () {
        /*==============================================================
         Back To Top
         =============================================================*/
        if ($(this).scrollTop() > 100) {
            $('.scrollup').show();
        } else {
            $('.scrollup').hide();
        }

        /*==============================================================
         Header Fixed Scroll
         =============================================================*/
        if ($(window).scrollTop() > 0) {
            $("#header-fix").addClass("active");
        } else {
            //remove the background property so it comes transparent again (defined in your css)
            $("#header-fix").removeClass("active");
        }
    });

})();
