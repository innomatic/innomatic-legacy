<?php
if ($handle = opendir('.')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
exec('convert "'.$entry.'" -colorspace gray "'.$entry.'"');
            echo "$entry\n";
        }
    }
    closedir($handle);
}