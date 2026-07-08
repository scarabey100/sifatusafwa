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

/*$options = $GLOBALS['magictoolbox_temp_options'];*/
$magicscrollOptions = $GLOBALS['magictoolbox_temp_magicscroll_options'];
$magicscroll = $GLOBALS['magictoolbox_temp_magicscroll'];
$main = $GLOBALS['magictoolbox_temp_main'];
$thumbs = $GLOBALS['magictoolbox_temp_thumbs'];
$pid = $GLOBALS['magictoolbox_temp_pid'];
if (!empty($magicscroll) && !empty($magicscrollOptions)) {
    $magicscrollOptions = " data-options=\"{$magicscrollOptions}\"";
} else {
    $magicscrollOptions = '';
}
?>
<!-- Begin magiczoomplus -->
<div class="MagicToolboxContainer selectorsBottom minWidth">
    <?php echo $main; ?>
<?php

if (is_array($thumbs)) {
    $thumbs = array_unique($thumbs);
}

if (count($thumbs) > 1) {
    ?>
    <div class="MagicToolboxSelectorsContainer">
        <div id="MagicToolboxSelectors<?php echo $pid ?>" class="<?php echo $magicscroll ?>"<?php echo $magicscrollOptions ?>>
        <?php echo join("\n\t", $thumbs); ?>
        </div>
    </div>
    <?php
}
?>
</div>
<!-- End magiczoomplus -->
