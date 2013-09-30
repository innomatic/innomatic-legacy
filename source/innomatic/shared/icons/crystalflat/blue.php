<?php
if ($handle = opendir('.')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
exec('convert "'.$entry.'" +level-colors \'#0a2d9e\', "'.$entry.'"');
            echo "$entry\n";
        }
    }
    closedir($handle);
}