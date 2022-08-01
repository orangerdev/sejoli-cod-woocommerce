<?php ob_start(); ?>

<form id="shipment-tracking-form" method="POST" action="">
  	<label for="shipment-number"><?php echo __('Shipment Number', 'scod-shipping'); ?></label>
  	<input type="text" id="shipment-number" name="shipment-number" value="">
  	<br>
  	<input type="submit" name="submit-tracking" value="Search" >
</form>';
 
<div id="shipment-history"></div>

<?php
	$html = ob_get_contents();
	ob_end_clean();
?>	