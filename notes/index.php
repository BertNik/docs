<?php

$dirs = scandir('./');

echo '<ul>';
foreach($dirs as $k => $v){
    if(!is_dir($v) && preg_match( ",txt$,", $v)){
	echo '<li>' . $v . '</li>';	
    }
}
echo '</ul>';
