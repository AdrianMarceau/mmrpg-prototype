
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

            // Define a parent container variable to help with scope
            var $parentContainer = $thisAdminForm;

            // Define a function for creating new audio objects and adding them to a index for caching purposes
            var audioObjectIndex = [];
            function newAudioObject(audioPath){
                var sound = new Howl({
                    src: [audioPath],
                    volume: 0.5,
                    loop: true
                    });
                audioObjectIndex.push(sound);
                return (audioObjectIndex.length - 1);
                }
            function getAudioObject(key){
                if (typeof audioObjectIndex[key] === 'undefined'){ return false; }
                return audioObjectIndex[key];
                }

            // Define a variable to keep track of which audio objects are currently playing
            var audioCurrentlyPlaying = [];
            function addToCurrentlyPlaying(audioID){
                var index = audioCurrentlyPlaying.indexOf(audioID);
                if (index > -1){ return; }
                audioCurrentlyPlaying.push(audioID);
                };
            function removeFromCurrentlyPlaying(audioID){
                var index = audioCurrentlyPlaying.indexOf(audioID);
                if (index > -1){ audioCurrentlyPlaying.splice(index, 1); }
                };
            function updateCurrentlyPlaying(){
                //console.log('updateCurrentlyPlaying()');
                //console.log('audioCurrentlyPlaying =', audioCurrentlyPlaying.length, audioCurrentlyPlaying);
                if (!audioCurrentlyPlaying.length){ return; }
                for (var i = 0; i < audioCurrentlyPlaying.length; i++){
                    var audioID = audioCurrentlyPlaying[i];
                    var audioObject = getAudioObject(audioID);
                    var $audioPlayer = $('.audio-player[data-audio-id="' + audioID + '"]', $parentContainer);
                    var $timerWidget = $('.widget.timer', $audioPlayer);
                    if ($timerWidget.length){
                        var audioPosition = audioObject.seek();
                        var audioDuration = audioObject.duration();
                        // convert the position to a 0:00:00 format
                        var minutes = Math.floor(audioPosition / 60);
                        var seconds = Math.floor(audioPosition - minutes * 60);
                        var milliseconds = Math.floor((audioPosition - Math.floor(audioPosition)) * 100);
                        var audioPositionText = minutes + ':' + (seconds < 10 ? '0' : '') + seconds + ':' + (milliseconds < 10 ? '0' : '') + milliseconds;
                        // convert the duration to a 0:00 format
                        var minutes = Math.floor(audioDuration / 60);
                        var seconds = Math.floor(audioDuration - minutes * 60);
                        var audioDurationText = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                        // Update the timer widget
                        var newMarkup = '/';
                        newMarkup = '<span class="current">'+audioPositionText+'</span>' + newMarkup;
                        newMarkup = newMarkup + '<span class="total">'+audioDurationText+'</span>';
                        $timerWidget.html(newMarkup);
                        }

                }
                // Loop through each audio player on the page and update it's state
                requestAnimationFrame(updateCurrentlyPlaying);
            }
            requestAnimationFrame(updateCurrentlyPlaying);

            // This function will be responsible for figuring out which button was clicked
            function audioButtonClicked(){

                // Collect references to the audio button itself and the parent player element
                var $audioButton = $(this);
                var $audioPlayer = $audioButton.closest('.audio-player');

                // Collect the current state of the player and the new state from the button
                var audioKind = $audioPlayer.attr('data-audio-kind');
                var audioPath = $audioPlayer.attr('data-audio-path');
                var audioID = $audioPlayer.attr('data-audio-id');
                var audioStateCurrent = $audioPlayer.attr('data-audio-state');
                var audioStateNew = $audioButton.attr('data-audio-control');
                //console.log('Audio button clicked!', { 'audioKind': audioKind, 'audioPath': audioPath, 'audioStateCurrent': audioStateCurrent, 'audioStateNew': audioStateNew });

                // If the button clicked is the same as the current state, do nothing
                if (audioStateNew === audioStateCurrent){
                    if (audioStateNew === 'play'){ audioStateNew = 'pause'; }
                    else if (audioStateNew === 'pause'){ audioStateNew = 'play'; }
                    else if (audioStateNew === 'stop'){ return; }
                    else { return; }
                }

                // Call the function responsible for updating the state and relevant elements
                updateAudioState(audioStateNew, audioID, $audioPlayer, $audioButton);

                };

            // This function will be responsible for updating the state and relevant elements
            function updateAudioState(audioStateNew, audioID){
                //console.log('updateAudioState(', audioStateNew, ',', audioID, ')');

                // Derive the $audioPlayer and $audioButton from the audioID
                var $audioPlayer = $('.audio-player[data-audio-id="' + audioID + '"]', $parentContainer);
                var $audioButton = $audioPlayer.find('.audio-button[data-audio-control="' + audioStateNew + '"]');

                // Update the audio state of the player container to reflect the change
                $audioPlayer.attr('data-audio-state', audioStateNew);

                // If this is a "play" request but other audio is playing, stop it
                if (audioStateNew === 'play'
                    && audioCurrentlyPlaying.length){
                    //console.log('PAUSE other clips');
                    //console.log('audioCurrentlyPlaying =', audioCurrentlyPlaying);
                    for (var i = 0; i < audioCurrentlyPlaying.length; i++){
                        var id = audioCurrentlyPlaying[i];
                        if (id === audioID){ continue; }
                        updateAudioState('pause', id);
                        }
                    }

                // Apply the play state to the audio object itself
                var audioObject = getAudioObject(audioID);
                if (audioStateNew === 'play'){
                    //console.log('PLAY the current clip');
                    audioObject.play();
                    addToCurrentlyPlaying(audioID);
                }
                if (audioStateNew === 'pause'){
                    //console.log('PAUSE the current clip');
                    audioObject.pause();
                    removeFromCurrentlyPlaying(audioID);
                }
                if (audioStateNew === 'stop'){
                    //console.log('STOP the current clip');
                    audioObject.stop();
                    removeFromCurrentlyPlaying(audioID);
                    $audioPlayer.find('.widget.timer .current').html('0:00');
                }

                // Update any currently playing audio players with new details
                updateCurrentlyPlaying();

                // Return now that we're done
                return;

                };

            // Loop through each music link trigger and add the player to the page
            $audioPlayers.each(function(){
                var $audioPlayer = $(this);
                var thisKind = $audioPlayer.attr('data-kind');
                var thisPath = $audioPlayer.attr('data-path');
                var audioID = newAudioObject(thisPath);
                $audioPlayer.removeAttr('data-kind');
                $audioPlayer.removeAttr('data-path');
                $audioPlayer.empty();
                $audioPlayer.attr('data-audio-kind', thisKind);
                $audioPlayer.attr('data-audio-path', thisPath);
                $audioPlayer.attr('data-audio-state', 'stop');
                $audioPlayer.attr('data-audio-id', audioID);
                $audioPlayer.append('<span class="button play" data-audio-control="play"><i class="fa fas fa-play-circle"></i></span>');
                $audioPlayer.append('<span class="button pause" data-audio-control="pause"><i class="fa fas fa-pause-circle"></i></span>');
                $audioPlayer.append('<span class="button stop" data-audio-control="stop"><i class="fa fas fa-stop-circle"></i></span>');
                $audioPlayer.append('<span class="widget timer"><span class="current">0:00</span>/<span class="total">0:00</span></span>');
                $audioPlayer.append('<span class="widget state"><i class="fa fas fa-music"></i></span>');
                $('.button[data-audio-control]', $audioPlayer).bind('click', function(e){
                    e.preventDefault();
                    audioButtonClicked.call(this);
                    });
                });

            }


    });


})();