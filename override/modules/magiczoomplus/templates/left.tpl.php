<?php
/**
* 2005-2017 Magic Toolbox
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Magic Toolbox <support@magictoolbox.com>
*  @copyright Copyright (c) 2017 Magic Toolbox <support@magictoolbox.com>. All rights reserved
*  @license   https://www.magictoolbox.com/license/
*/

$options = $GLOBALS['magictoolbox_temp_options'];
$magicscrollOptions = $GLOBALS['magictoolbox_temp_magicscroll_options'];
$magicscroll = $GLOBALS['magictoolbox_temp_magicscroll'];
$main = $GLOBALS['magictoolbox_temp_main'];
$thumbs = $GLOBALS['magictoolbox_temp_thumbs'];
$pid = $GLOBALS['magictoolbox_temp_pid'];
$selectorMaxWidth = (int)$options->getValue('selector-max-width');
if (!empty($magicscroll) && !empty($magicscrollOptions)) {
    $magicscrollOptions = " data-options=\"{$magicscrollOptions}\"";
} else {
    $magicscrollOptions = '';
}
?>
<!-- Begin magiczoomplus -->
<div class="MagicToolboxContainer selectorsLeft minWidth<?php echo empty($magicscroll)?' noscroll':'' ?>">
<?php

if (is_array($thumbs)) {
    $thumbs = array_unique($thumbs);
}

if (count($thumbs) > 1) {
    ?>
    <div class="MagicToolboxSelectorsContainer" style="flex-basis: <?php echo $selectorMaxWidth ?>px; width: <?php echo $selectorMaxWidth ?>px;">
        <div id="MagicToolboxSelectors<?php echo $pid ?>" class="<?php echo $magicscroll ?>"<?php echo $magicscrollOptions ?>>
        <?php echo join("\n\t", $thumbs); ?>
        </div>
    </div>
    <?php
        if (!empty($magicscroll) && !is_numeric($options->getValue('height'))) {
            ?>
            <script type="text/javascript">
                mzOptions = mzOptions || {};
                mzOptions.onUpdate = function() {
                    MagicScroll.resize('MagicToolboxSelectors<?php echo $pid ?>');
                };
            </script>
            <?php
        }
        ?>
    <?php
}
?>
    <div class="MagicToolboxMainContainer">
        <?php echo $main; ?>
    </div>
</div>
<script type="text/javascript">
    if (window.matchMedia("(max-width: 767px)").matches) {
        $scroll = document.getElementById('MagicToolboxSelectors<?php echo $pid ?>');
        if ($scroll && typeof $scroll != 'undefined') {
            $attr = $scroll.getAttribute('data-options');
            if ($attr !== null) {
                $scroll.setAttribute('data-options',$attr/*.replace(/autostart *\: *false/gm,'')*/.replace(/orientation *\: *[a-zA-Z]{1,}/gm,'orientation:horizontal'));
                if (typeof mzOptions != 'undefined') {
                    mzOptions.onUpdate = function() {};
                }
            }
        }
    } else {

    }
</script>
<!-- End magiczoomplus -->
