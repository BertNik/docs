<?php
$noteDir = "./notes/";
$noteExtension = ".txt";
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
        return str_replace(".txt", "", $a);
    };
    $noExtension = array_map($removeExt, array_filter($dirs, $removeHidden));
    die(json_encode(array('success' => json_encode( array_values($noExtension) ))));
}else if(isset($_GET['filename']) && $_GET['filename'] === ''){ 
    $text =  json_decode($_POST['data'],true)['text'];
    $time = time();
    $name = './notes/'.$time.'.txt'; 
    $fileCreated = file_put_contents($name, $text);
    echo json_encode(array('success' => $fileCreated ? $time : $fileCreated)); 
}