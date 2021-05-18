<?php
require_once "layout.inc.php";
Starthtml::show('Emil Bratt Børsting');
Header::show(basename($_SERVER['SCRIPT_NAME']));
?>
<?php

// IMAGE BASE PATH
$img_category_dir = __DIR__."/images/gallery/converted/*";

// RESOLUTION
$small = '400';
$medium = '1200';
$large = '2400';
$all_small_res = array(
    'landscape' => array(),
    'wide' => array(),
    'portrait' => array(),
);
$img_all_surface_res = array();
$img_all_desktop_res = array();

?>

<div class="greybox">
<picture class="greyboximg">
<source media="(min-width:1200px)" loading="lazy" srcset="images/converted/landskap/1600/landscape.set-0005.png,">
<source media="(min-width:800px)" loading="lazy" srcset="images/converted/dyr/1200/landscape.set-0003.png">

 <img class="responsive_image" src="images/converted/insekter/800/landscape.set-0002.png" loading="lazy">
</picture>
</div>


<div class="greybox">

<p>hei</p>



</div>


<?php
Footer::show();
Endhtml::show();
?>
