<?php
// get checkout var received from shortcode
$checkout = $_tpv_checkout;

// build initial data of the process
$_user_logged  = is_user_logged_in();
$_user         = !$_user_logged ? false : wp_get_current_user();
$_user_id      = !$_user_logged ? false : $_user->ID;
$_user_info    = !$_user_logged ? false : woo_template_get_all_user_info( $_user_id );
$_data = [
  'user' => $_user_info,
];

$_path = woo_template_get_partial_path_of_local_dir(__DIR__);
?>

<div class="custom-checkout-process" id="custom-checkout-process" >
  
  <div class="user-data-container" id="user-data-container" data-chekout-step='<?php echo json_encode( $_data ); ?>' ></div>
  
  <div class="custom-checkout-process_left">
  
    <div class="checkout-steps">
      
      <div class="checkout-step step step-access">
        <?php get_template_part_with_query_vars( $_path . '/step-part-title' , ['_tpv_title' => "1. INFORMAZIONI D'ACCESSO"]); ?>
        <?php get_template_part( $_path . '/step-maybe-exists' ); ?>
        <?php get_template_part( $_path . '/step-login' ); ?>
        <?php get_template_part( $_path . '/step-create-user' ); ?>
        <?php get_template_part( $_path . '/step-part-maybe-edit' ); ?>
      </div>
      
      <div class="checkout-step step step-shipping-info">
        <?php get_template_part_with_query_vars( $_path . '/step-part-title' , ['_tpv_title' => "2. DATI DI SPEDIZIONE"]); ?>
        <?php get_template_part( $_path . '/step-shipping-info' ); ?>
        <?php get_template_part( $_path . '/step-part-maybe-edit' ); ?>
      </div>
      
      <div class="checkout-step step step-billing-info">
        <?php get_template_part_with_query_vars( $_path . '/step-part-title' , ['_tpv_title' => "2. DATI DI FATTURAZIONE"]); ?>
        <?php get_template_part( $_path . '/step-billing-info' ); ?>
        <?php get_template_part( $_path . '/step-part-maybe-edit' ); ?>
      </div>
            
      <div class="checkout-step step step-payment">
        <?php get_template_part_with_query_vars( $_path . '/step-part-title' , ['_tpv_title' => "3.PAGAMENTO"]); ?>
        <?php get_template_part( $_path . '/step-payment' ); ?>
        <?php get_template_part( $_path . '/step-part-maybe-edit' ); ?>
      </div>

    </div>

  </div>

  <div class="custom-checkout-process_right">
  
    <div class="checkout-cart">     
      <?php get_template_part( $_path . '/maybe-edit-cart' ); ?>
      <?php get_template_part( $_path . '/cart' ); ?>
    </div>

    <?php get_template_part( $_path . '/order-review' ); ?>
  
  </div>
  
  <style>
    .custom-checkout-process {
      display: flex;
    }
    .custom-checkout-process_left {
      flex-grow: 1; 
    }
    .custom-checkout-process_right {
      margin-left: 2em;
      flex: 0 0 300px;
    }
    .site-content .custom-checkout-process_right {
      margin-top: 0;
    }
    .checkout-steps > * + * {
      margin-top: 2em;
    }

    .checkout-step {
      background-color: #efefef;
      padding: 1em;    
    }
    .checkout-step > * {
      margin: 1em;   
    }
    .checkout-step form {

    }
    .checkout-step .maybe-edit {
      padding: 1em;
      position: relative;
    }
    .checkout-step .maybe-edit > * {
      margin: 1.5em;
      display: block;
    }
    .checkout-step .maybe-edit .summary{

    }
    .checkout-step .maybe-edit .trigger{
      position: absolute;
      right: 0;
      top: 0;
      text-decoration: underline;
    }

    .checkout-cart {
      padding: 1em;
      border: solid 1px black;
    }
    .checkout-cart .maybe-edit-cart {
      text-align: right;
    }

    .checkout-cart-list {
      
    }
    .checkout-cart-list .cart-item {
      padding: 1em;
      display: flex;
    }
    .checkout-cart-list .cart-item .image {
      max-width: 100px;
      margin-right: 1em;
    }
    .checkout-cart-list .cart-item .details {
      flex-grow:1;
      display: flex;
      flex-direction:column;
      justify-content: space-evenly;
      text-align: left;
      align-items: flex-start;

    }
  </style>
  <script>
    jQuery(document).ready(function ($) {
      setTimeout(CHECKOUT_STEPS_SCRIPT, 1);
    });
  </script>

</div>