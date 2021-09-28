<?php

class WT_USER_REGISTER_FORM extends WT_COMPONENT {

  private static $self = 'WT_USER_REGISTER_FORM';

  protected static $__DIR__ = __DIR__;

  protected static $template = '/templates/register_form';

  /* =================================================== 
        INIT
  =================================================== */

  public static function init() {
  }


  
}

add_action( 'init', [ 'WT_USER_REGISTER_FORM' , 'init'] );

?>