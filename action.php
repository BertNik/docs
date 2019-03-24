<?php
register_shutdown_function( "fatal_handler" );

class Main{
    
    private $noteDir = "./notes/", $noteExtension = ".txt";
    
    public function __construct($arg=null){
        if($arg === 'cli'){
            $numberArgs = count($_SERVER['argv']);
            if($numberArgs > 1){
                $args = array_splice($_SERVER['argv'], 1);
                $this->testCases($args[0]);
            }  
        }
        $action = $this->determineAction();
        if($action['isGetData']){
            $this->getData();
        }else if($action['isSaveData']){
            $this->saveData();
        }else if($action['isGetListItems']){
            $this->getListItems();
        }else if($action['isCreateNote']){ 
            $this->createNote();
        }else if($action['isDelete']){
            $this->deleteNote();
        }
    
    }
    private function determineAction(){

        $noFileName = isset($_GET['filename']) && $_GET['filename'] !== '';
        $isGetData = isset($_GET['cmd']) && $_GET['cmd'] === 'getData' && $noFileName;
        $isSaveData = isset($_GET['cmd']) && $_GET['cmd'] === 'save' && $noFileName;
        $isGetListItems = isset($_GET['cmd']) && $_GET['cmd'] === 'getListItems';
        $isCreateNote = isset($_GET['filename']) && $_GET['filename'] === '';
        $isDelete = $_GET['filename'] && isset($_GET['cmd']) 
                    && $_GET['filename'] !== '' && $_GET['cmd'] === 'delete' 
                    && $_SERVER['REQUEST_METHOD'] === 'DELETE';
        
        return array(
            'noFileName' => $noFileName,
            'isGetData' => $isGetData,
            'isSaveData' => $isSaveData,
            'isGetListItems' => $isGetListItems,
            'isCreateNote' => $isCreateNote,
            'isDelete' => $isDelete
        );
    }
    public function getData(){
        $file = $this->noteDir.$_GET['filename'].$this->noteExtension;
        $data = file_get_contents($file);
        die(json_encode(array('success' => $data)));
    }
    public function saveData(){
        $warning = "";
        $name = $_GET['filename'];
        $text =  json_decode($_POST['data'],true)['text'];
        if(base64_encode(base64_decode($text)) === $text){
            $fileCreated = file_put_contents($this->noteDir.$name.$this->noteExtension, $text);
        }else{
            $text = base64_decode($text);
            $warning = "data saved may have been corrupted.";
            $fileCreated = file_put_contents($this->noteDir.$name.$this->noteExtension, $text);
        }
        die(json_encode(array('success' => $fileCreated ? $name : $fileCreated, 'warning' => $warning)));
    }
    public function getListItems(){
        $dirs = scandir($this->noteDir);
        $removeHidden = function($dirs){
            return !preg_match(',^\.,i',$dirs) && !preg_match(',php$,i',$dirs);
        };
        $removeExt = function($a){
            return str_replace($this->noteExtension, "", $a);
        };
        $noExtension = array_map($removeExt, array_filter($dirs, $removeHidden));
        die(json_encode(array('success' => json_encode( array_values($noExtension)))));
    }
    public function createNote(){
        $text =  json_decode($_POST['data'],true)['text'];
        $time = time();
        $name = $this->noteDir.$time.$this->noteExtension; 
        $fileCreated = file_put_contents($name, $text);
        die(json_encode(array('success' => $fileCreated ? $time : $fileCreated))); 
    }
    public function deleteNote(){
        $name = $_GET['filename'];
        $fileDeleted = unlink($this->noteDir.$name.$this->noteExtension);
        die(json_encode(array('success' => $fileDeleted )));
    }
    private function testCases($arg){
        $definedCases = [
            'noFileName',
            'isGetData',
            'isGetData_fail',
            'isSaveData',
            'isSaveData_fail',
            'isCreateNote',
            'isDelete',
            'isDelete_fail',
            'listTestCases'
        ];
        switch($arg){
            case 'noFileName':
                $_GET['filename'] = '';
                break;
            case 'isGetData':
                $_GET['filename'] = "testfilename";
                $_GET['cmd'] = 'getData';
                break;
            case 'isGetData_fail':
                $_GET['filename'] = "";
                $_GET['cmd'] = 'getData';
                break;
            case 'isSaveData':
                $_GET['cmd'] = 'save';
                $_GET['filename'] = "filename";
                break;
            case 'isSaveData_fail':
                $_GET['cmd'] = 'save';
                $_GET['filename'] = "";
                break;
            case 'isCreateNote':
                $_GET['filename'] = "";
                break;
            case 'isDelete':
                $_GET['filename'] = 'filename';
                $_GET['cmd'] = 'delete';
                $_SERVER['REQUEST_METHOD'] = 'DELETE';
                break;
            case 'isDelete_fail':
                $_GET['filename'] = '';
                $_GET['cmd'] = 'delete';
                $_SERVER['REQUEST_METHOD'] = 'DELETE';
                break;
            case 'listTestCases':
                die(var_dump($definedCases));
                break;
            default:
                echo sprintf("%s\n", $arg);
                break;
        }
    }
}
if(php_sapi_name() == "cli"){
    $m = new Main('cli');
}else{
    $m = new Main();
}

function fatal_handler(){
    $error = error_get_last();
    if ($error['type'] === E_ERROR) { 
        var_dump(debug_backtrace()); 
    } 
}

