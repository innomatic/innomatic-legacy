<?php
if ($main_page_url == 'main') {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Innomatic - <?php echo InnomaticContainer::instance('innomaticcontainer')->getCurrentDomain()->domaindata['domainname']; ?></title>
<link rel="shortcut icon"
    href="<?php echo InnomaticContainer::instance('innomaticcontainer')->getBaseUrl(false);?>/favicon.ico"
    type="image/x-icon" />
</head>
<?php
require_once('innomatic/desktop/layout/DesktopLayout.php');
$layout_mode = DesktopLayout::instance('desktoplayout')->getLayout();

if ($layout_mode == 'horiz') {
    ?>
<frameset rows="35,90,*" framespacing="0" border="0" frameborder="0">
        <frame name="header" target="main" src="logo" scrolling="no" noresize>
        <frame name="menu" target="main" src="menu" scrolling="auto" noresize>
    <frame name="main" src="<?php echo $main_page_url; ?>" noresize>
    <noframes>
    <body>
    <p align="center"><strong>Your browser doesn't support frames.</strong></p>
    </body>
    </noframes>
</frameset>
    <?php
} else {
    ?>
<frameset cols="150,*" framespacing="0" border="0" frameborder="0">
    <frameset rows="110,*" framespacing="0" border="0" frameborder="0">
        <frame name="header" target="main" src="logo" scrolling="no" noresize>
        <frame name="menu" target="main" src="menu" scrolling="auto" noresize>
    </frameset>
    <frame name="main" src="<?php echo $main_page_url; ?>" noresize>
    <noframes>
    <body>
    <p align="center"><strong>Your browser doesn't support frames.</strong></p>
    </body>
    </noframes>
</frameset>
<?php
}
?>

</html>
<?php
} else {

}
?>