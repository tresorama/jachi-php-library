<?php

/* NOTE:
The simplest way to catch exceptions is through the use of a generic try-catch block.
Because exceptions are objects, they all extend a built-in Exception class (see Throwing Exceptions in PHP),
which means that catching every exception thrown is as simple as type-hinting the global exception object, 
which is indicated by adding a backslash in front.

*/

try {
  // do your stuff    
} catch ( \Custom\Exception $e ) {
  // ...
} catch ( \Other\Exception $e ) {
  // ...
} catch ( \Exception $e ) {
    
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

  // uncomment for debug
  // echo $message;

  // append this error message to error file
  error_log($message);
  
}
