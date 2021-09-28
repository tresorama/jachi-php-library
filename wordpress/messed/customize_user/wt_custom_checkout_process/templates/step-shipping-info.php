<?php

$path = woo_template_get_partial_path_of_local_dir(__DIR__);

?>

<form id="cs-ship" class="cs-ship" >

  <div id="cs-ship-errors"></div>

  <div class="field form-row">
    <label class="name" for="cs-ship-first-name">Nome</label>
    <div class="content">
      <input type="text" name="cs-ship-first-name" id="cs-ship-first-name" placeholder="Nome">
    </div>
  </div>

  <div class="field form-row">
    <label class="name" for="cs-ship-last-name">Cognome</label>
    <div class="content">
      <input type="text" name="cs-ship-last-name" id="cs-ship-last-name" placeholder="Cognome">
    </div>
  </div>

  <?php get_template_part( $path . '/shipping-calculator' ) ; ?>

  <div class="field form-row">
    <label class="name" for="cs-ship-address">Indirizzo</label>
    <div class="content">
      <input type="text" name="cs-ship-address" id="cs-ship-address" placeholder="Indirizzo">
    </div>
  </div>

  <div class="field form-row">
    <label class="name" for="cs-ship-co">C/O</label>
    <div class="content">
      <input type="text" name="cs-ship-co" id="cs-ship-co" placeholder="C/O">
    </div>
  </div>

  <div class="field form-row">
    <label class="name" for="cs-ship-phone">Telefono</label>
    <div class="content">
      <input type="tel" name="cs-ship-phone" id="cs-ship-phone" placeholder="Telefono">
    </div>
  </div>

  <div class="button" id="cs-ship-submit" >PROCEDI</div>

</form>
