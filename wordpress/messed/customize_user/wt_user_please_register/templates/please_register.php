<?php

// REMEMEBER THAT USER IS NOT LOGGED !

// get "My account" page permalink
$my_account_url = get_permalink( get_option( 'woocommerce_myaccount_page_id') );
$my_account_url = ensure_link_ends_without_slash( $my_account_url );
// get "login" and "register" permalink ( they are )
$login_url    = apply_filters( 'woo_template_login_page_permalink', $my_account_url . '#login' ); // .../my-account#login
$register_url = apply_filters( 'woo_template_register_page_permalink', $my_account_url . '#register' ); // .../my-account#register

?>

<div class="plaese-register-target">
  
  <div class="close-button-wrapper plaese-register-closer"><div class="close-button"></div></div>
  
  <div class="inner">
    
    <?php WT_USER_WHY_REGISTER_IS_GOOD::render(); ?>
    
    <a class="button register-button" href=<?php echo esc_url( $register_url ); ?>>Registrati</a>
    
    <p class="maybe-login">Sei gia nostro cliente ? <a href=<?php echo esc_url( $login_url ); ?>>Accedi</a></p>
  
  </div>  
  
</div>