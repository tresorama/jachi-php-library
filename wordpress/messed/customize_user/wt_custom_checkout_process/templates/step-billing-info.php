<?php

$path = woo_template_get_partial_path_of_local_dir(__DIR__);

?>

<form id="cs-billing" class="cs-billing" >

	<div class="maybe-change-billing-address">
  
    <label for="same-data">
			<input type="radio" name="billing-same-as-shipping" id="same-data" value="same-data" checked="checked"/>
			I dati di fatturazione sono gli stessi dei dati di spedizione. 
		</label>
		
    <label for="change-data">
			<input type="radio" name="billing-same-as-shipping" id="change-data" value="change-data"/>
			I dati di fatturazione sono diversi.
		</label>

		
	</div>
  
  <div class="billing-address-form">
    <?php get_template_part( woo_template_get_partial_path_of_local_dir(__DIR__) . '/billing-form' ); ?>
  </div>
  
  <div class="button" id="cs-billing-submit" >PROCEDI</div>

</form>