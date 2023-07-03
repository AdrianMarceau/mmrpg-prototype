
/* -- BACK-END JAVASCRIPT (ADMIN > EDIT MUSIC) -- */

(function(){

    // Define global object variables
    var $thisAdmin = false;
    var $thisAdminForm = false;
    var $thisAdminSearch = false;
    var $thisAdminResults = false;
    var $thisAdminEditor = false;
    var thisRootURL = '/';
    var $adminHome = false;
    var $adminForm = false;
    var $adminAjaxForm = false;
    var $adminAjaxFrame = false;
    var baseRootURL = typeof window.mmrpgConfigRootURL !== 'undefined' ? window.mmrpgConfigRootURL : '/';
    var adminRootURL = baseRootURL + 'admin/';

    // Wait for document ready before delegating events
    $(document).ready(function(){

        // Collect references to key objects
        $thisAdmin = $('#admin');
        $thisAdminForm = $('.adminform', thisAdmin);
        $thisAdminSearch = $('.adminform > .search', thisAdmin);
        $thisAdminResults = $('.adminform > .results', thisAdmin);
        $thisAdminEditor = $('.adminform > .editor', thisAdmin);
        $adminHome = $('.adminhome', thisAdmin);
        $adminForm = $('.adminform form.form', thisAdmin);
        $adminAjaxForm = $('.adminform form[name="ajax-form"]', thisAdmin);
        $adminAjaxFrame = $('.adminform iframe[name="ajax-frame"]', thisAdmin);

        //console.log('I am the edit music script!');
        //console.log('baseRootURL =', baseRootURL);
        //console.log('adminRootURL =', adminRootURL);

        // Check to see if there are any music player triggers on the page
        var $audioPlayers = $('.audio-player[data-path]', $thisAdminForm);
        //var $musicLinks = $('a[href*=".mp3"],a[href*=".ogg"]', $thisAdminForm);
        if ($audioPlayers.length){
            //console.log('There are ', $audioPlayers.length, 'audio players on this page');
            mmrpgAdminAudioPlayer($audioPlayers, {});
            }

    });

})();