(function($) {
    var _invokeAd = function() {
        var container = $('<div></div>')
                .addClass('ad-interstitial-overlay')
                .css('opacity', 0),
            ad_container = $('<div><img src="http://placehold.it/768x768" /></div>') // AD CODE INSERTED HERE
                .addClass('ad-interstitial'),
            ad_closer = $('<a href="#">&times; close</a>')
                .addClass('ad-interstitial-close')
                .on('click', function(e) {
                    e.preventDefault();
                    $(this).parents('.ad-interstitial-overlay').remove();
                });

        container.append(ad_container).append(ad_closer);

        $('body').append(container);

        var leftOffset = 0,
            topOffset = 0;

        container.width($('body').width());
        container.height($(window).height());

        leftOffset = (container.width() / 2) - (ad_container.width() / 2);
        topOffset = (container.height() / 2) - (ad_container.height() / 2);

        ad_container.css({'top': topOffset, 'left': leftOffset});

        container.animate({'opacity': 1}, {'duration': 100});
    }

    $(function() {
        var _DEBUG = true,
            _IDENTIFIER = 'IMMASWEETUNIQUEIDENTIFIERLIKEAUUIDORSOMETHING';  // A UNIQUE SITE IDENTIFIER HERE

        var aid = "{{article.uid}}",                                        // ITEM IDENTIFIER HERE
            data = $.cookie(_IDENTIFIER),
            show = false,
            now = new Date;

        if( !data || !data.length ) {
            data = {};
        } else {
            data = JSON.parse(data);
        }

        if( _DEBUG ) { console.log('data', data) }

        if( data.previous && data.previous != aid ) {
            if( data.previous && data.previous != aid && data.counter >= 5 ) {
                if( _DEBUG ) { console.log('Counter limit') }
                show = true;
            } else if( data.lastaccess && data.lastaccess < now.getTime() - 86400000 ) {
                if( _DEBUG ) { console.log('24 hour limit') }
                show = true;
            }
        } else if( !data.previous || !data.counter ) {
            if( _DEBUG ) { console.log('No previous or empty counter') }
            show = true;
        }

        var counter = 1;
        if( data.counter && data.counter < 5 && data.previous != aid ) {
            counter = data.counter + 1;
        }

        data = {
            previous: aid,
            counter: counter,
            lastaccess: now.getTime()
        };

        $.cookie(_IDENTIFIER, JSON.stringify(data), {expires: 2, path: '/'});
        if( show ) {
            if( _DEBUG ) {
                console.log('Show ad');
            } else {
                _invokeAd();
            }
        }
    });
})($);