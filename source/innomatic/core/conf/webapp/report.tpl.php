<?php echo '<?xml version="1.0" encoding="UTF-8"? >'."\n";?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?=$title;?> - Error report</title>
<style><!--
* { font-family: Arial, Helvetica, Verdana, Arial, Geneva, sans-serif }
h1 { color: white; font-weight: 500; background-color: #2b6991; font-size:22px; padding: 8px; }
h2 { color: white; font-weight: 500; background-color: #2b6991; font-size:16px; padding: 4px; }
h3 { color: white; font-weight: 500; background-color: #2b6991; font-size:14px; padding: 4px; }
body { color: #7f7e7e; background-color: white; }
b { color: black; font-weight: 500; background-color: #eeeeee; }
p { background: white; font-weight: 300; color: #7f7e7e; font-size:12px; }
a { color: #7f7e7e; }
hr { color: #525D76;}
-->
</style>
</head><body>
<h1>Innomatic - HTTP Status <?=$status_code.' - '.$message;?></h1>
<hr size="1" noshade="noshade" />
<p><b>type</b> <?=(is_null($e) ? 'Status report' : 'Exception report' );?></p>
<p><b>message</b> <u><?php
        if (!is_null($e)) {
            echo $e->getmessage();
        } else {
            echo $message;
        }
?></u></p>
<p><b>description</b> <u><?=$report;?></u></p>
<?php
        if (!is_null($e)) {
            echo '<p><b>exception</b> <pre>' . $e . '</pre></p>';
        }
?>
<hr size="1" noshade="noshade" />
<h3><?=$server_info;?></h3>
</body>
</html>
