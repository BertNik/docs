<?php
$noteDir = "./notes/";
$noteExtension = ".txt";
if(isset($_GET['filename']) && $_GET['filename'] !== ''){
    if(isset($_GET['cmd']) && $_GET['cmd'] === 'getData'){
        $file = $noteDir.$_GET['filename'].$noteExtension;
        $data = file_get_contents($file);
        echo json_encode(array('success' => $data));
	    die;
    }
    $name = $_GET['filename'];
    $text =  json_decode($_POST['data'],true)['text'];
    $fileCreated = file_put_contents($noteDir.$name.$noteExtension, $text);
    echo json_encode(array('success' => $fileCreated ? $name : $fileCreated));
}else{
    $text =  json_decode($_POST['data'],true)['text'];
    $time = time();
    $name = './notes/'.$time.'.txt'; 
    $fileCreated = file_put_contents($name, $text);
    echo json_encode(array('success' => $fileCreated ? $time : $fileCreated)); 
}
