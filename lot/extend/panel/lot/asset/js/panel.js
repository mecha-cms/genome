(function($, win, doc) {

    (function() {
        var $tabs = $('.tab-button'), $id;
        if (!$tabs) return;
        $tabs.on("click", function() {
            $id = (this.hash || "").replace('#', "");
            $id = ($id && $('#' + $id)) || $(this).closest('.tab').find('.tab-content').eq($(this).index());
            $(this).addClass('current').siblings().removeClass('current');
            if ($id) {
                $id[$(this).hasClass('toggle') ? 'toggleClass' : 'removeClass']('hidden').siblings().addClass('hidden');
            }
            return false;
        });
    })();

})(jQuery, window, document);