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
$main = $GLOBALS['magictoolbox_temp_main'];
$thumbs = $GLOBALS['magictoolbox_temp_thumbs'];
$pid = $GLOBALS['magictoolbox_temp_pid'];
?>
<!-- Begin magiczoomplus -->
<div class="mt-images-container">
    <div class="product-cover">
      <?php echo $main; ?>
    </div>
<?php
if (count($thumbs) > 0) {
    ?>
    <div class="MagicToolboxSelectorsContainer js-qv-mask mask">
      <ul id="MagicToolboxSelectors<?php echo $pid ?>" class="product-images js-qv-product-images">
          <li class="thumb-container" style="white-space: nowrap;">
            <?php echo join("</li>\n\t<li class=\"thumb-container\" style=\"white-space: nowrap\">", $thumbs); ?>
          </li>
      </ul>
    </div>
    <?php
}
?>
</div>
<!-- End magiczoomplus -->
