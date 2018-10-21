	<div class="col-sm-10 col-sm-offset-1">
		<h3><i class="fa fa-clock-o fa-fw" aria-hidden="true"></i>&nbsp;Recent Timezone Form-up Frequency</h3>
	</div>
	<?php
	$TZ_counts = array(
		'EMEA' => NULL,
		'AMER' => NULL,
		'APAC' => NULL
	);
	foreach( $recent_favoured_scheduled_timezones as $TZ_data )
	{
		$TZ_counts[$TZ_data->TZ] = $TZ_data;
	} ?>
	<div class="col-sm-4 aligncenter">
		<span style="font-size: 26px;">EMEA<br>
		12:00&nbsp;-&nbsp;20:00<br>
		<?php echo $TZ_counts['EMEA'] == NULL ? '0' : $TZ_counts['EMEA']->count; ?></span> fleets<br>
		<br>
	</div>
	<div class="col-sm-4 aligncenter">
		<span style="font-size: 26px;">AMER<br>
		20:00&nbsp;-&nbsp;04:00<br>
		<?php echo $TZ_counts['AMER'] == NULL ? '0' : $TZ_counts['AMER']->count; ?></span> fleets<br>
		<br>
	</div>
	<div class="col-sm-4 aligncenter">
		<span style="font-size: 26px;">APAC<br>
		04:00&nbsp;-&nbsp;12:00<br>
		<?php echo $TZ_counts['APAC'] == NULL ? '0' : $TZ_counts['APAC']->count; ?></span> fleets<br>
		<br>
	</div>