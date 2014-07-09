$(document).ready(function(){
    /* Capture mouse position
    mouseX = 0;
    mouseY = 0;
    $(document).mousemove(function(e){
        mouseX = e.pageX; 
        mouseY = e.pageY;
    });
    */

    /* Window width and height
    windowX = $(window).width();
    windowY = $(window).height();
    */

    /* Window width and height
    documentX = $(document).width();
    documentY = $(document).height();
    */

    /* The vertical scroll position is the same as the number of pixels 
        that are hidden from view above the scrollable area.
    scrollPos = $(window).scrollTop();
    */

    /* Sample JSON Ajax call. Remove dataType for a normal Ajax call.
    $.ajax({
        type: "GET",  
        url: "ajax.php",  
        data: "action=doSomething",
        dataType: "json",
        success: function(json) {
            $('#container').html(json['output']);
        }
    });
    */

    // Dragable dialog window.
    //$(".fade-in").dialog({width:640, height:480});
});
