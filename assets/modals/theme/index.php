<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 25-Nov-16
 * Time: 12:30
 */

include '../../php-lib/Theme.php';

$themes = Theme::getAllThemes();
$current_theme = Theme::getAppliedTheme();
?>
<div class="modalHeader">Themes</div>
<div class="modalBody">
    <div class="col-left mCustomScrollbar">
        <ul class="multiselect" id="themes-list">
            <?php
            foreach ($themes as $theme) {
                $id = $theme->getId();
                $class = $id == $current_theme->getId() ? 'active' : '';
                echo "<li data-id='$id' class='$class'>", $theme->getName(), "</li>";
            }
            ?>
        </ul>
    </div>
</div>
<script>
    function bindClicks() {
        $('#themes-list').find('li').click(function () {
            var el = $(this);
            var id = el.attr('data-id');

            $.ajax('/assets/modals/theme/ajax/set_theme.php?id=' + id)
                .done(function (data) {
                    if (data == 'success') {
                        alert("Theme applied successfully");

                        setTimeout(function () {
                            reloadCSS();
                            el.siblings().removeClass('active');
                            el.addClass('active');
                        }, 250);

                    } else {
                        error(data);
                    }
                })
                .fail(function (x, xx) {
                    error("Failed to change theme. " + xx);
                })
                .always();
        });
    }

    function reloadCSS() {
        var queryString = '?reload=' + new Date().getTime();
        $('link[rel="stylesheet"]').each(function () {
            this.href = this.href.replace(/\?.*|$/, queryString);
        });
    }

    bindClicks();
</script>
