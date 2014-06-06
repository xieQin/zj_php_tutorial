<?php 
 renderView("Tag","head",$_viewData); 
?>

<p>m3 start</p>

<div>
<?php
print_r($_viewData);
echo "\n";
echo appF1();
echo "\n";
?>
</div>
<div><a href="<?= UA("Index/index") ?>"> index </a></div>
<script type="text/javascript" src="<?php echo U("Public/app.js")  ?>"></script>
<p>m3 end</p>