<?php ob_start(); ?>

<h6><?php echo __('Number Resi:', 'scod-shipping'); ?></h6>
<div class="shipping-number" style="font-size:26px;"><b><?php echo $params['shipmentNumber']; ?></b></div>

<h6><?php echo __('Shipping Details:', 'scod-shipping'); ?></h6>
<table style="text-align: left;">
<tr>
	<th><?php echo __('Courier:', 'scod-shipping'); ?></th>
	<td>JNE - <?php echo $trace_tracking_arveoli->cnote->cnote_services_code; ?></td>
</tr>
<tr>
	<th><?php echo __('Total Price:', 'scod-shipping'); ?></th>
	<td><?php echo sejolisa_price_format( $trace_tracking_arveoli->cnote->cnote_amount ); ?></td>
</tr>
<tr>
	<th><?php echo __('Weight:', 'scod-shipping'); ?></th>
	<td><?php echo $trace_tracking_arveoli->cnote->cnote_weight; ?> kg</td>
</tr>
<tr>
	<th><?php echo __('Send Date:', 'scod-shipping'); ?></th>
	<td><?php echo date_i18n( 'F d, Y H:i:s', strtotime( $trace_tracking_arveoli->cnote->cnote_date ) ); ?></td>
</tr>
<tr>
	<th><?php echo __('From:', 'scod-shipping'); ?></th>
	<td><?php echo $trace_tracking_arveoli->detail[0]->cnote_shipper_name; ?></td>
</tr>
<tr>
	<th><?php echo __('Shipper Address:', 'scod-shipping'); ?></th>
	<td><?php echo $trace_tracking_arveoli->detail[0]->cnote_shipper_addr1. ' ' .$trace_tracking_arveoli->detail[0]->cnote_shipper_addr2; ?></td>';
</tr>
<tr>
	<th><?php echo __('To:', 'scod-shipping'); ?></th>
	<td><?php echo $trace_tracking_arveoli->cnote->cnote_receiver_name; ?></td>
</tr>
<tr>
	<th><?php echo __('Receiver Address:', 'scod-shipping'); ?></th>
	<td><?php echo $trace_tracking_arveoli->cnote->city_name; ?></td>
</tr>
<tr>
	<th><?php echo __('Receiver:', 'scod-shipping'); ?></th>
	<td><?php echo $trace_tracking_arveoli->cnote->cnote_pod_receiver.' - '.date_i18n( 'F d, Y H:i:s', strtotime$trace_tracking_arveoli->cnote->cnote_pod_date ) ); ?></td>';
</tr>
<tr>
	<th><?php echo __('Last Status:', 'scod-shipping'); ?></th>
	<td><?php echo $trace_tracking_arveoli->cnote->last_status; ?></td>
</tr>
</table>

<h6><?php echo __('Tracking History:', 'scod-shipping'); ?></h6>
<table style="text-align: left;">
<tr>
	<th><?php echo __('Date', 'scod-shipping'); ?></th>
	<th colspan="2"><?php echo __('Description', 'scod-shipping'); ?></th>
</tr>
<?php
foreach ($trace_tracking_arveoli->history as $history) {
?>
<tr>
		<td><?php echo date_i18n( 'F d, Y H:i:s', strtotime( $history->date ) ); ?></td>
		<td colspan="2"><?php echo $history->desc; ?></td>
	</tr>
<?php } ?>
</table>

<?php
	$html = ob_get_contents();
	ob_end_clean();
?>	