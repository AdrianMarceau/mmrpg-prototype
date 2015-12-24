

var searchPage = false;
var searchForms = false;
var searchTimeout = false;
var searchRequest = false;
var searchData = {};

$(document).ready(function(){

    searchPage = $('.page_admin');
    searchForms = $('form[data-search]', searchPage);

    $('input[name=text]', searchForms).bind('keyup', function(){

        //console.log('collect search params!');

        var searchInput = $(this);
        var searchForm = searchInput.parents('form[data-search]');
        var searchResults = searchForm.find('.results');

        var newSearchData = {};
        newSearchData['type']  = searchForm.attr('data-search');
        newSearchData['text'] = searchInput.val();

        var otherForms = searchForms.filter('form[data-search!='+newSearchData['type']+']');
        otherForms.find('.results').empty();
        $('#mmrpg .search_results').remove();

        if (newSearchData['text'] == ''){ searchResults.empty(); }

        if (searchTimeout != false){
            //console.log('kill search timeout!');
            clearTimeout(searchTimeout);
            searchTimeout = false;
            }

        if (!newSearchData['type'].length){ return false; }
        else if (!newSearchData['text'].length || newSearchData['text'].length < 2){ return false; }

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
                        searchResults.empty();
                    }

                    }
                });

            }, 600);

    });



});