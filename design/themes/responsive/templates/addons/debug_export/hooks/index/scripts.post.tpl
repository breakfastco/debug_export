{literal}
<script>
(function(_, $) {
    $.ceEvent('on', 'ce.commoninit', function() {
        var $menu = $('#DebugToolbar .deb-menu');
        if (!$menu.length || $menu.find('a[href*="debug_export.debug_export"]').length) {
            return;
        }
        var $link = $menu.find('a[href*="debugger_hash="]').first();
        if (!$link.length) {
            return;
        }
        var match = $link.attr('href').match(/[?&]debugger_hash=([^&]+)/);
        if (!match) {
            return;
        }
        var exportHref = window.location.origin + '/index.php?dispatch=debug_export.debug_export&debugger_hash=' + encodeURIComponent(match[1]);
        var $item = $('<li><a></a></li>');
        $item.find('a')
            .attr('href', exportHref)
            .attr('target', '_blank')
            .text('Export')
            .append('<small>Download reports as JSON</small>');
        $menu.append($item);
    });
})(Tygh, Tygh.$);
</script>
{/literal}
