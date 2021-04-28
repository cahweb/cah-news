// Lazy load images on page by replacing src with data-src attribute after page load
/*
document.addEventListener('DOMContentLoaded', function() {
    var lazyImages = document.querySelectorAll('img[data-src]'); 
    console.log(lazyImages); 
    lazyImages.forEach(function(image) {
        image.setAttribute('src', image.getAttribute('data-src')); 
        image.onload = function () {
            image.removeAttribute('data-src'); 
            image.removeAttribute('width'); 
            image.removeAttribute('height'); 
        }
    }); 

}); 
*/

(function($) {
    $(document).ready(function() {
        console.log('Page loaded!');
        let lazyImages = $('img[data-src]');
        $(lazyImages).each(function(index, img) {
            $(img).attr({src: $(img).attr('data-src')});
            $(img).on('load', function() {
                $(this).removeAttr('data-src');
                $(this).removeAttr('width');
                $(this).removeAttr('height');
            });
        });
    });
})(jQuery);