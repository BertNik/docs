<?php
include_once './includes.php';

use ActionTests as at;

class Action{
    
    private $noteDir = "./notes/", 
            $noteExtension = ".txt", 
            $isCli = false;
    
    public function __construct($arg=null){
        if($arg === 'cli'){
            $this->isCli = true;
            $testCases = new at\Action_Tests();
            $numberArgs = count($_SERVER['argv']);
            if($numberArgs > 1){
                $args = array_splice($_SERVER['argv'], 1);
                $testCases::test($args[0], $args);
            }  
        }
        $response = $this->actions();
        die( sprintf("%s\n", $response ) );
    }
    private function actions(){
        $action = $this->determineAction();
        $case = array_intersect($action,[true]);
        if(count($case) > 1) return 'too many cases';
        switch(array_keys($case)[0]){
            case 'isGetData':
                return $this->getData();
            case 'isSaveData':
                return $this->saveData();
            case 'isGetListItems':
                return $this->getListItems();
            case 'isCreateNote':
                return $this->createNote();
            case 'isDelete':
                return $this->deleteNote();
            default:
                return 'no valid arguments passed';
        }
    }
    private function determineAction(){

        $noFileName = isset($_GET['filename']) && $_GET['filename'] !== '';
        $isGetData = isset($_GET['cmd']) && $_GET['cmd'] === 'getData' && $noFileName;
        $isSaveData = isset($_GET['cmd']) && $_GET['cmd'] === 'save' && $noFileName;
        $isGetListItems = isset($_GET['cmd']) && $_GET['cmd'] === 'getListItems';
        $isCreateNote = isset($_GET['filename']) && $_GET['filename'] === '';
        $isDelete = isset($_GET['filename']) && $_GET['filename'] && isset($_GET['cmd']) 
                    && $_GET['filename'] !== '' && $_GET['cmd'] === 'delete' 
                    && $_SERVER['REQUEST_METHOD'] === 'DELETE';
        
        
        return array(
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
        return (json_encode(array('success' => $data)));
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
        return (json_encode(array('success' => $fileCreated ? $name : $fileCreated, 'warning' => $warning)));
    }
    public function getListItems(){
        $dirs = scandir($this->noteDir);
        $removeHidden = function($dirs){
            return !preg_match(',^\.,i',$dirs) && !preg_match(',php$,i',$dirs) && preg_match(',.txt$,i',$dirs);
        };
        $removeExt = function($a){
            return str_replace($this->noteExtension, "", $a);
        };
        $noExtension = array_map($removeExt, array_filter($dirs, $removeHidden));
        return (json_encode(array('success' => json_encode( array_values($noExtension)))));
    }
    public function createNote(){
        $text =  json_decode($_POST['data'],true)['text'];
        $time = time();
        $name = $this->noteDir.$time.$this->noteExtension; 
        $fileCreated = file_put_contents($name, $text);
        return (json_encode(array('success' => $fileCreated !== FALSE ? $time : $fileCreated))); 
    }
    public function deleteNote(){
        $name = $_GET['filename'];
        $fileDeleted = unlink($this->noteDir.$name.$this->noteExtension);
        return (json_encode(array('success' => $fileDeleted )));
    }
}
if(php_sapi_name() == "cli"){
    $m = new Action('cli');
}else{
    $m = new Action();
}