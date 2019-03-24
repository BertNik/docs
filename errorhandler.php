<?php
register_shutdown_function( "fatal_handler" );
function fatal_handler(){
    $error = error_get_last();
    if ($error['type'] === E_ERROR) { 
        var_dump(debug_backtrace()); 
    } 
}