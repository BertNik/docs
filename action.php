<?php
register_shutdown_function( "fatal_handler" );

class Main{
    
    private $noteDir = "./notes/", $noteExtension = ".txt", $allTestCases = false, $isCli = false,
            $definedCases = array();
    
    public function __construct($arg=null){
        if($arg === 'cli'){
            $this->isCli = true;
            $numberArgs = count($_SERVER['argv']);
            if($numberArgs > 1){
                $args = array_splice($_SERVER['argv'], 1);
                $this->testCases($args[0]);
            }  
        }
        $response = $this->actions();
        die( sprintf("%s\n", $response ) );
    }
    private function actions(){
        $action = $this->determineAction();
        if($action['isGetData']){
            return $this->getData();
        }else if($action['isSaveData']){
            return $this->saveData();
        }else if($action['isGetListItems']){
            return $this->getListItems();
        }else if($action['isCreateNote']){ 
            return $this->createNote();
        }else if($action['isDelete']){
            return $this->deleteNote();
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
            return !preg_match(',^\.,i',$dirs) && !preg_match(',php$,i',$dirs);
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
    private function testCases($arg){
        
        $this->definedCases = [
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
        $runTest = function($case){
            switch($case){
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
                    $data = array('text'=> base64_encode('test'));
                    $_POST['data'] = json_encode($data);
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
                    die(var_dump($this->definedCases));
                    break;
                default:
                    echo sprintf("%s\n", $arg);
                    break;
            }
            if($this->allTestCases) {
                $locallyDefinedVars = get_defined_vars();
                var_dump(
                        sprintf("%s", 
                                json_encode( 
                                                array( 'defined_vars' => $locallyDefinedVars, 
                                                    'GET' => $_GET, 
                                                    'POST' => $_POST,
                                                    'SERVER[REQUEST_METHOD]' => $_SERVER['REQUEST_METHOD']
                                                ), JSON_PRETTY_PRINT
                                            )
                                )
                        );
            }
        };
        if($arg === 'allTestCases'){
            $this->allTestCases = true;
            unset($this->definedCases[count($this->definedCases)-1]);
            array_map($runTest, $this->definedCases);
        }else{
            $runTest($arg);
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

