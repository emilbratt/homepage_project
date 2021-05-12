<?php
require_once "layout.inc.php";
Starthtml::show('Emil Bratt Børsting');
Header::show(basename($_SERVER['PHP_SELF']));
?>

<picture>
<source media="(max-width:800px)" srcset="images/converted/landskap/800/landscape.set-0005.png,">
<source media="(max-width:1600px)" srcset="images/converted/landskap/1600/landscape.set-0005.png">

  <img src="images/converted/landskap/2400/landscape.set-0005.png" loading="lazy">
</picture>

<!-- <img class="" loading="lazy" src="images/converted/landskap/1200/landscape.set-0005.png" alt="Bildet kan ikke vises" /> -->


<?php
Endhtml::show();
?>
