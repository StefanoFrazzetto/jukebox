<?php
/**
 * Created by PhpStorm.
 * User: Vittorio
 * Date: 25-Nov-16
 * Time: 12:30.
 */
require_once '../../../vendor/autoload.php';

use Lib\Theme;

$themes = Theme::getAllThemes();
$current_theme = Theme::getAppliedTheme();
?>
<div class="modalHeader">Themes</div>
<div class="modalBody">
    <div class="col-left mCustomScrollbar" style="max-height: 300px; overflow: hidden;">
        <ul class="multiselect" id="themes-list">
            <?php
            foreach ($themes as $theme) {
                $id = $theme->getId();
                $current_theme_id = $current_theme != null ? $current_theme->getId() : 0;
                $class = $id == $current_theme_id ? 'active' : '';
                $delete = '';

                if (!$theme->isIsReadOnly()) {
                    $delete = "<i class='fa fa-trash right clickable'></i>";
                }

                echo "<li data-id='$id' class='$class'>", $theme->getName(), $delete, '</li>';
            }
            ?>
        </ul>
    </div>
    <div class="col-right">
        <button onclick="modal.openPage('/assets/modals/theme/add_theme.php')">Create theme</button>
    </div>
</div>
<script>
    function bindClicks() {
        $('#themes-list')
            .find('li').click(function () {
            var el = $(this);
            var id = el.attr('data-id');

            $.ajax('/assets/modals/theme/ajax/set_theme.php?id=' + id)
                .done(function (data) {
                    if (data == 'success') {
                        alert("Theme applied successfully.");

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
        })
            .find('i').click(function (e) {
            e.stopPropagation();
            var elem = $(this).parent();
            var id = elem.attr('data-id');

            $.ajax('/assets/modals/theme/ajax/delete_theme.php?id=' + id)
                .done(function (data) {
                    if (data == 'success') {
                        alert("Theme deleted successfully.");
                        elem.remove();
                    } else {
                        error(data);
                    }
                })
                .fail(function (x, xx) {
                    error("Failed to change theme. " + xx);
                })
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
