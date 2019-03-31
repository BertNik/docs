<?php
namespace ActionTests;
class Action_Tests{

    private static  $allTestCases = false,
                    $definedCases = array();

    public function __construct(){
        return $this;
    }

    public static function test($arg, $allArgs){
        
        self::$definedCases = [
            'noFileName',
            'isGetData',
            'isGetData_fail',
            'isSaveData',
            'isSaveData_fail',
            'isCreateNote',
            'isDelete',
            'isDelete_fail',
            'allTestCases',
            'listTestCases',
            
        ];
        $runTest = function($case, $allArgs){
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
                    $data = array('text'=> base64_encode('test'));
                    $_POST['data'] = json_encode($data);
                    $_SERVER['REQUEST_METHOD'] = 'DELETE';
                    break;
                case 'listTestCases':
                    die(var_dump(self::$definedCases));
                    break;
                case 'regexFileName':
                    $pat = ",/,";
                    $sub = $allArgs[1];
                    $out = preg_match($pat, $sub, $matches);
                    if($out){
                        die(var_dump(preg_replace($pat, "_", $sub)));
                    }else{
                        die('no match');
                    }
                default:
                    echo isset($arg) ? sprintf("%s\n", $arg) : '[no arg]';
                    break;
            }
            if(self::$allTestCases) {
                $locallyDefinedVars = get_defined_vars();
                var_dump(
                        sprintf("%s", 
                                json_encode( 
                                                array( 'defined_vars' => $locallyDefinedVars, 
                                                    'GET' => $_GET, 
                                                    'POST' => $_POST,
                                                    'SERVER[REQUEST_METHOD]' => isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : ''
                                                ), JSON_PRETTY_PRINT
                                            )
                                )
                        );
            }
        };
        if($arg === 'allTestCases'){
            self::$allTestCases = true;
            unset(self::$definedCases[count(self::$definedCases)-1]);
            array_map($runTest, self::$definedCases);
        }else{
            $runTest($arg, $allArgs);
        }
    }
}