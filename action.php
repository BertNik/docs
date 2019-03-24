<?php
register_shutdown_function( "fatal_handler" );



class Main{
    
    private $noteDir = "./notes/", $noteExtension = ".txt";
    public function __construct(){
        $noteDir = $this->noteDir;
        $noteExtension = $this->noteExtension;
        $warning = "";
        if(isset($_GET['filename']) && $_GET['filename'] !== ''){
            if(isset($_GET['cmd']) && $_GET['cmd'] === 'getData'){
                $file = $noteDir.$_GET['filename'].$noteExtension;
                $data = file_get_contents($file);
                die(json_encode(array('success' => $data)));
            }else if(isset($_GET['cmd']) && $_GET['cmd'] === 'save'){
                $name = $_GET['filename'];
                $text =  json_decode($_POST['data'],true)['text'];
                if(base64_encode(base64_decode($text)) === $text){
                    $fileCreated = file_put_contents($noteDir.$name.$noteExtension, $text);
                }else{
                    $text = base64_decode($text);
                    $warning = "data saved may have been corrupted.";
                    $fileCreated = file_put_contents($noteDir.$name.$noteExtension, $text);
                }
                die(json_encode(array('success' => $fileCreated ? $name : $fileCreated, 'warning' => $warning)));
            }
        }else if(isset($_GET['cmd']) && $_GET['cmd'] === 'getListItems'){
            $dirs = scandir($noteDir);
            $removeHidden = function($dirs){
                return !preg_match(',^\.,i',$dirs) && !preg_match(',php$,i',$dirs);
            };
            $removeExt = function($a){
                $noteExtension = $this->noteExtension;
                return str_replace($noteExtension, "", $a);
            };
            
            $noExtension = array_map($removeExt, array_filter($dirs, $removeHidden));
            die(json_encode(array('success' => json_encode( array_values($noExtension) ))));
        }else if(isset($_GET['filename']) && $_GET['filename'] === ''){ 
            $text =  json_decode($_POST['data'],true)['text'];
            $time = time();
            $name = $noteDir.$time.$noteExtension; 
            $fileCreated = file_put_contents($name, $text);
            die(json_encode(array('success' => $fileCreated ? $time : $fileCreated))); 
        }
        
        if(isset($_GET['filename']) && isset($_GET['cmd']) 
                    && $_GET['filename'] !== '' && $_GET['cmd'] === 'delete' 
                    && $_SERVER['REQUEST_METHOD'] === 'DELETE'){
            $name = $_GET['filename'];
            $fileDeleted = unlink($noteDir.$name.$noteExtension);
            die(json_encode(array('success' => $fileDeleted )));
        }
        
    }
}

$m = new Main();

function fatal_handler(){
    $error = error_get_last();
    // fatal error, E_ERROR === 1
    if ($error['type'] === E_ERROR) { 
        var_dump(debug_backtrace()); 
    } 
    
}