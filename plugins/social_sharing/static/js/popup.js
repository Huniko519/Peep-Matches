$(document).ready(function() {

$('#share_popup_wrap').click(function(e) 
    {
    $('#share_popup_wrap').fadeOut();
    });

$('#share_more_btn').click(function(e) 
    {
    $('#share_popup_wrap').fadeIn();
    });
$('#share_close').click(function(e) 
    {
    $('#share_popup_wrap').fadeOut();
    });

$('#share_side_close').click(function(e) 
    {
    $('.home_share_btns').fadeOut();
    });
});


