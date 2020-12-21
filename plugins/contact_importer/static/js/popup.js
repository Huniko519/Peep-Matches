$(document).ready(function() {

 if(localStorage.getItem('popState') != 'shown'){
        $("#invite_popup_wrap").delay(2000).fadeIn();
        localStorage.setItem('popState','shown')
    }
 
  
    $('#invite_popup_close').click(function(e) 
    {
    $('#invite_popup_wrap').fadeOut(); 
    });
     $('#invite_popup_close').click(function(e) 
    {
    $('#invite_popup_wrap').fadeOut(); 
    });
    $('#got_it_close').click(function(e) 
    {
    $('#after_close').fadeOut();
    });
$('#invite_popup_close').click(function(e) 
    {
    $('#after_close').fadeIn();
    });
$('#mailing_tab').click(function(e) 
    {
    $('#mailing_cust').fadeIn();
    });
$('#mailing_close').click(function(e) 
    {
    $('#mailing_cust').fadeOut();
    });
});



