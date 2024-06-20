jQuery(document).ready( function( $ ) {
    var i_fancy = $('.i_fancy a, .i_fancy_a');
    i_fancy.fancybox({
        maxWidth	: 800,
        maxHeight	: 800,
        fitToView	: false,
        width		: '70%',
        height		: '95%',
        autoSize	: false,
        closeClick	: false,
        openEffect	: 'none',
        closeEffect	: 'none',
        padding: 0
    });
});

// Read a page's GET URL variables and return them as an associative array.
function getUrlVars()
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}