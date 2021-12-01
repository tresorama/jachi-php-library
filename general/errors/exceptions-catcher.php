<?php

  try {
  
    // do your stuff
  
  } catch ( $e ) {
    
    $message = sprintf(
      'Errore !!!.
      Codice Errore: "%s"
      Linea : "%s"
      File : "%s"
      Il messagio è il seguente :
      <<< %s >>>
      ',
      $e->getCode(),
      $e->getLine(),
      $e->getFile(),
      $e->getMessage(),
    );
    
    echo $message;
  
  }
