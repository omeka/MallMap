(function($) {
    $(document).ready(function() {
        var filterToggle = 1;
        $('#filter-button').click(function(e) {
            e.preventDefault();
            if (filterToggle == 1) {
                $('#filters').animate({
                    left: '-=30%'
                }, 200, 'linear');
                filterToggle = 0;
                console.log(filterToggle);
            } else {
                $('#filters').animate({
                    left: '+=30%'
                }, 200, 'linear');
                filterToggle = 1;
                console.log(filterToggle);
            }
        });
    });
}) (jQuery);
