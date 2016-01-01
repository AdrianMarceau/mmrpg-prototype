

var searchPage = false;
var searchForms = false;
var searchTimeout = false;
var searchRequest = false;
var searchData = {};

$(document).ready(function(){

    searchPage = $('.page_admin');
    searchForms = $('form[data-search]', searchPage);

    $('input[name=text]', searchForms).each(function(){

        var thisInput = $(this);
        var thisParent = thisInput.parent();
        var thisClear = $('<a class="clear disabled">&#x2717;</a>');
        thisClear.appendTo(thisParent);
        thisClear.bind('click', function(){
            thisClear.addClass('disabled');
            thisInput.val('').trigger('keyup');
            });

        });

    $('input[name=text]', searchForms).bind('keyup', function(){

        //console.log('collect search params!');

        var searchInput = $(this);
        var searchInputParent = searchInput.parent();
        var searchForm = searchInput.parents('form[data-search]');
        var searchResults = searchForm.find('.results');
        var clearButton = searchInputParent.find('a.clear');
        clearButton.removeClass('disabled');

        var newSearchData = {};
        newSearchData['type']  = searchForm.attr('data-search');
        newSearchData['text'] = searchInput.val();
        newSearchData['text'] = newSearchData['text'].replace(/\s+$/, '*').replace(/^\s+/, '*');

        var otherForms = searchForms.filter('form[data-search!='+newSearchData['type']+']');
        otherForms.find('.results').empty();
        $('#mmrpg .search_results').remove();

        if (newSearchData['text'] == ''){
            searchResults.empty();
            clearButton.addClass('disabled');
            }

        if (searchTimeout != false){
            //console.log('kill search timeout!');
            clearTimeout(searchTimeout);
            searchTimeout = false;
            }

        if (!newSearchData['type'].length){ return false; }
        else if (!newSearchData['text'].length || newSearchData['text'].length < 1){ return false; }

        //console.log('create search timeout!');

        searchTimeout = setTimeout(function(){

            searchResults.empty();

            if (searchRequest != false){
                //console.log('kill search request!');
                searchRequest.abort();
                searchRequest = false;
                }

            //console.log('create post request!');

            searchRequest = $.ajax('admin/search/', {
                type: 'post',
                data: newSearchData,
                success: function(data){

                    searchData = newSearchData;

                    data = data.length ? data.split('\n') : [];
                    var status = data.shift();
                    var message = data.shift();
                    var request = data.shift();
                    var markup = data.join('\n');

                    //console.log('status = '+status);
                    //console.log('message = '+message);
                    //console.log('request = '+request);
                    //console.log('markup = '+markup);

                    searchResults.append('<div class="message">'+message+'</div>');
                    searchResults.append(markup);

                    if (searchResults.is(':visible')){
                        var resultsOffset = searchResults.offset();
                        var cloneResults = searchResults.clone();
                        cloneResults
                            .removeClass('results')
                            .addClass('search_results').css({
                                top:resultsOffset.top,
                                right:resultsOffset.right,
                                bottom:resultsOffset.bottom,
                                left:resultsOffset.left,
                                width:searchResults.width()
                                });

                        $('#mmrpg .search_results').remove();
                        cloneResults.appendTo('#mmrpg');
                        var rList = cloneResults.find('.list');
                        var rHead = cloneResults.find('.head');
                        rList.perfectScrollbar();
                        if (rList.hasClass('ps-active-y')){ rHead.addClass('ps-active-y'); }
                        searchResults.empty();
                    }

                    }
                });

            }, 600);

    });



});