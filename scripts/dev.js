
/* -- MAP GENERATOR JAVASCRIPT -- */

$(document).ready(function(){

    var fieldOptions = $('#window .field_options');
    var fieldSubmit = $('input[type="button"]', fieldOptions);

    fieldSubmit.bind('click', function(e){
        e.preventDefault();
        var actionBase = fieldOptions.find('form').attr('action');
        var scaleValue = fieldOptions.find('select[name="scale"]').val();
        var playerValue = fieldOptions.find('select[name="player"]').val();
        var fieldValue = fieldOptions.find('select[name="field"]').val();
        var bossValue = fieldOptions.find('select[name="boss"]').val();
        //console.log('actionBase', actionBase);
        //console.log('scaleValue', scaleValue);
        //console.log('playerValue', playerValue);
        //console.log('fieldValue', fieldValue);
        //console.log('bossValue', bossValue);
        var redirectURL = [actionBase];
        if (scaleValue.length){ redirectURL.push('scale='+scaleValue); }
        if (playerValue.length){ redirectURL.push('player='+playerValue); }
        if (fieldValue.length){ redirectURL.push('field='+fieldValue); }
        if (bossValue.length){ redirectURL.push('boss='+bossValue); }
        //console.log('redirectURL', redirectURL);
        redirectURL = redirectURL.join('&');
        //console.log('redirectURL', redirectURL);
        window.location.href = redirectURL;
        return true;
        });


    var fieldMap = $('#window .field_map');
    var eventGrid = $('.event_grid', fieldMap);

    $('.cell', eventGrid).bind('click', function(e){
        e.preventDefault();
        if (!$(this).hasClass('complete')){
            $(this).addClass('complete');
            } else {
            $(this).removeClass('complete');
            }
        });

});