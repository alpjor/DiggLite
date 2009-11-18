(function() {
    $(document).ready(function() {
        $('#container-form').change(function() {
            //$('.input > select').attr('disabled', 'disabled');
            $(this).submit();
            return false;
        });

        // If they are logged in no need to bind the digg buttons!
        if (!loggedin) {
            return;
        }

        $('.digg-it').click(function() {
            var el = this;
            var id = el.id.split('-').pop();
            $.ajax({
                type: 'POST',
                url: 'digg.php',
                dataType: 'json',
                data: ({ story_id: id }),
                success: function(res) {
                    if (res.error) {
                        alert(res.error);
                        return;
                    }

                    $(el).html('<span>dugg</span>');
                    var count = $(el).siblings().children('a').children('strong');
                    count.html((count.html() * 1) + 1);
                    $(el).unbind();
                }
            });

            return false;
        });

        $('.bury-link').click(function() {
            var el = this;
            var id = el.id.split('-').pop();
            $.ajax({
                type: 'POST',
                url: 'bury.php',
                dataType: 'json',
                data: ({ story_id: id }),
                success: function(res) {
                    if (res.error) {
                        alert(res.error);
                        return;
                    }

                    $(el).closest('.story').fadeOut().unbind();
                }
            });

            return false;
        });
    });
})();
