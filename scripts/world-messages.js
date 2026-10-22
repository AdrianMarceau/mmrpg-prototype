
// -- MESSAGE HELPER METHODS -- //

// Define a quick function for showing a message (perhaps for an item pickup) immediately in the map world UI
// (first by  queuing a world message to be shown when next ready, rather than all at once)
function showWorldMessage(messageText, showAfter, $insertAfter){
    //console.log('%c' + 'mmrpgWorldMap.showWorldMessage()', 'color: magenta;');
    //console.log('w/ messageText = ', messageText, '\n' + 'w/ showAfter = ' + showAfter + '\n' + 'w/ $insertAfter = ', $insertAfter);
    showAfter = showAfter || 0;
    $insertAfter = $insertAfter || null;
    let _self = this;
    let _selfRef = _self.showWorldMessage;
    if (typeof _selfRef.messagesQueue === 'undefined'){ _selfRef.messagesQueue = []; }
    _selfRef.messagesQueue.push({
        messageText: messageText,
        showAfter: showAfter,
        $insertAfter: $insertAfter
        });
    _self.__showNextWorldMessage();
    }
function __showNextWorldMessage(){
    //console.log('%c' + 'mmrpgWorldMap.__showNextWorldMessage()', 'color: magenta;');
    let _self = this;
    let _selfRef = _self.showWorldMessage;
    let _config = _self.config.mapMessages;
    let maxConcurrent = _config.maxConcurrent || 1;
    let queueStagger = typeof _config.nextQueueStagger === 'number' ? _config.nextQueueStagger : _config.queueStagger;
    let staggerDelay = typeof _config.nextStaggerDelay === 'number' ? _config.nexStaggerDelay : _config.staggerDelay;
    //console.log('_config.nextQueueStagger = ', _config.nextQueueStagger);
    //console.log('_config.queueStagger = ', _config.queueStagger);
    //console.log('queueStagger = ', queueStagger);
    if (typeof _selfRef.activeCount === 'undefined'){ _selfRef.activeCount = 0; }
    if (typeof _selfRef.isProcessing === 'undefined'){ _selfRef.isProcessing = false; }
    if (_selfRef.isProcessing === true) return;
    if (_selfRef.activeCount >= maxConcurrent) return;
    if (!_selfRef.messagesQueue || _selfRef.messagesQueue.length <= 0) return;
    let nextMessage = _selfRef.messagesQueue.shift();
    if (!nextMessage) return;
    let subCount = Array.isArray(nextMessage.messageText) ? nextMessage.messageText.length : 1;
    let timeUntilReadyForNext = (subCount > 1)
        ? ((subCount - 1) * staggerDelay) + queueStagger
        : queueStagger;
    _selfRef.isProcessing = true;
    setTimeout(function(){
        _selfRef.isProcessing = false;
        _self.__showNextWorldMessage();
        }, timeUntilReadyForNext);
    _selfRef.activeCount++;
    _selfRef.onMessagesComplete = function(){
        _selfRef.activeCount--;
        _self.__showNextWorldMessage();
        };
    _self.__actuallyShowWorldMessage(nextMessage);
    }
function __actuallyShowWorldMessage(messageData){
    //console.log('%c' + 'mmrpgWorldMap.__actuallyShowWorldMessage()', 'color: magenta;');
    let _self = this;
    let _config = _self.config.mapMessages;
    let _selfRef = _self.showWorldMessage;
    let $messageDisplay = _self.elements.messageDisplay;
    let $messageWrapper = $messageDisplay.find('.wrapper');
    let staggerDelay = typeof _config.nextStaggerDelay === 'number' ? _config.nexStaggerDelay : _config.staggerDelay;
    let holdDuration = typeof _config.nextHoldDuration === 'number' ? _config.nextHoldDuration : _config.holdDuration;
    let fadeDuration = typeof _config.nextFadeDuration === 'number' ? _config.nextFadeDuration : _config.fadeDuration;
    let rawText = messageData.messageText;
    let baseDelay = messageData.showAfter || 0;
    let $initialInsert = messageData.$insertAfter || null;
    let messages = Array.isArray(rawText) ? rawText : [rawText];
    let $lastBlock = $initialInsert;
    let batchKeys = []; // To track all IDs in this specific batch
    let lastRevealTime = 0;
    let batchTimestamp = Date.now();
    for (let i = 0; i < messages.length; i++){
        let text = messages[i];
        let messageKey = batchTimestamp + '_' + i;
        batchKeys.push(messageKey);
        let revealTime = baseDelay + (i * staggerDelay) + 100;
        if (revealTime > lastRevealTime) { lastRevealTime = revealTime; }
        let isSubtext = (i > 0) || ($lastBlock && $lastBlock.hasClass('message'));
        let messageClass = 'message pending' + (isSubtext ? ' subtext' : '');
        let messageMarkup = '<div class="' + messageClass + '" data-key="' + messageKey + '">' + text + '</div>';
        let $message = $(messageMarkup);
        if ($lastBlock){ $message.insertAfter($lastBlock); }
        else { $message.prependTo($messageWrapper); }
        $lastBlock = $message;
        (function(k, rt){
            setTimeout(function(){
                $messageWrapper.find('.message[data-key="' + k + '"]').removeClass('pending');
                }, rt);
            })(messageKey, revealTime);
        }
    let sharedHideTime = lastRevealTime + holdDuration;
    let sharedRemoveTime = sharedHideTime + fadeDuration;
    let nextMessageTime = sharedRemoveTime + 100;
    setTimeout(function(){
        for (let j = 0; j < batchKeys.length; j++){ $messageWrapper.find('.message[data-key="' + batchKeys[j] + '"]').addClass('hidden'); }
        }, sharedHideTime);
    setTimeout(function(){
        for (let j = 0; j < batchKeys.length; j++){ $messageWrapper.find('.message[data-key="' + batchKeys[j] + '"]').remove(); }
        }, sharedRemoveTime);
    setTimeout(function(){
        if (typeof _selfRef.onMessagesComplete === 'function'){ _selfRef.onMessagesComplete.call(_self); }
        }, nextMessageTime);
    if (!_selfRef.messagesQueue
        || _selfRef.messagesQueue.length === 0){
        delete _config.nextQueueStagger;
        delete _config.nextStaggerDelay;
        delete _config.nextHoldDuration;
        delete _config.nextFadeDuration;
        }
    return true;
    }

// Assign the sub-functions to the main class's prototype
mmrpgWorldMap.prototype.showWorldMessage = showWorldMessage;
mmrpgWorldMap.prototype.__showNextWorldMessage = __showNextWorldMessage;
mmrpgWorldMap.prototype.__actuallyShowWorldMessage = __actuallyShowWorldMessage;
