<?php

$dirs = scandir('./');

echo '<ul>';
foreach($dirs as $k => $v){
    if(!is_dir($v) && preg_match( ",txt$,", $v)){
    ?> 
    <a href="/?note=<?php echo str_replace(".txt", "", $v) ?>" >
    <?php
    echo '<li>' . $v . '</li>';	
    ?>
    </a>
    <?php
    }
}
echo '</ul>';
