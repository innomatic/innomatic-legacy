<?php
namespace Innomatic\Wui\Widgets\Layersmenu;

/**
* This is an extension of the base class of the PHP Layers Menu system.
*
* It provides other menu types, that to do not require JavaScript to work.
*
* @version 2.3.5
*
 * @package WUI
 */
class XLayersMenu extends LayersMenu
{
/**
* The default value of the expansion string for the PHP Tree Menu
* @var string
*/
public $phpTreeMenuDefaultExpansion;
/**
* An array where we store the PHP Tree Menu code for each menu
* @var array
*/
public $_phpTreeMenu;

/**
* The character used for the Plain Menu in the menu structure format to separate fields of each item
* @var string
*/
public $plainMenuSeparator;
/**
* The template to be used for the Plain Menu
*/
public $plainMenuTpl;
/**
* An array where we store the Plain Menu code for each menu
* @var array
*/
public $_plainMenu;

/**
* The character used for the Horizontal Plain Menu in the menu structure format to separate fields of each item
* @var string
*/
public $horizontalPlainMenuSeparator;
/**
* The template to be used for the Horizontal Plain Menu
*/
public $horizontalPlainMenuTpl;
/**
* An array where we store the Horizontal Plain Menu code for each menu
* @var array
*/
public $_horizontalPlainMenu;

/**
* The constructor method; it initializates some variables
* @return void
*/
function __construct()
{
    parent::__construct();
    $this->phpTreeMenuDefaultExpansion = "";
    $this->_phpTreeMenu = array();

    $this->plainMenuTpl = $this->dirroot . $this->tpldir . "layersmenu-plain_menu.ihtml";
    $this->plainMenuSeparator = "|";
    $this->_plainMenu = array();

    $this->horizontalPlainMenuTpl = $this->dirroot . $this->tpldir . "layersmenu-horizontal_plain_menu.ihtml";
    $this->horizontalPlainMenuSeparator = "|";
    $this->_horizontalPlainMenu = array();
}

/**
* The method to set the default value of the expansion string for the PHP Tree Menu
* @return void
*/
function setPHPTreeMenuDefaultExpansion($phpTreeMenuDefaultExpansion)
{
    $this->phpTreeMenuDefaultExpansion = $phpTreeMenuDefaultExpansion;
}

/**
* Method to prepare a new PHP Tree Menu.
*
* This method processes items of a menu and parameters submitted
* through GET (i.e. nodes to be expanded) to prepare and return
* the corresponding Tree Menu code.
*
* @param string $menu_name the name of the menu whose items have to be processed
* @return string
*/
function newPHPTreeMenu(
    $menu_name = ""    // non consistent default...
    ) {
    $protocol = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https://" : "http://";
    $this_host = (isset($_SERVER["HTTP_HOST"])) ? $_SERVER["HTTP_HOST"] : $_SERVER["SERVER_NAME"];
    if (isset($_SERVER["SCRIPT_NAME"])) {
        $me = $_SERVER["SCRIPT_NAME"];
    } elseif (isset($_SERVER["REQUEST_URI"])) {
        $me = $_SERVER["REQUEST_URI"];
    } elseif (isset($_SERVER["PHP_SELF"])) {
        $me = $_SERVER["PHP_SELF"];
    } elseif (isset($_SERVER["PATH_INFO"])) {
        $me = $_SERVER["PATH_INFO"];
    }
    $url = $protocol . $this_host . $me;
    $query = "";
    reset($_GET);
    while (list($key, $value) = each($_GET)) {
        if ($key != "p" && $value != "") {
            $query .= "&amp;" . $key . "=" . $value;
        }
    }
    if ($query != "") {
        $query = "?" . substr($query, 5) . "&amp;p=";
    } else {
        $query = "?p=";
    }
    $p = (isset($_GET["p"])) ? $_GET["p"] : $this->phpTreeMenuDefaultExpansion;

/* ********************************************************* */
/* Based on TreeMenu 1.1 by Bjorge Dijkstra (bjorge@gmx.net) */
/* ********************************************************* */
    $this->_phpTreeMenu[$menu_name] = "";

    $img_space        = $this->imgwww . "tree_space." . $this->treeMenuImagesType;
    $alt_space        = "  ";
    $img_vertline        = $this->imgwww . "tree_vertline." . $this->treeMenuImagesType;
    $alt_vertline        = "| ";
    $img_expand        = $this->imgwww . "tree_expand." . $this->treeMenuImagesType;
    $alt_expand        = "+-";
    $img_expand_corner    = $this->imgwww . "tree_expand_corner." . $this->treeMenuImagesType;
    $alt_expand_corner    = "+-";
    $img_collapse        = $this->imgwww . "tree_collapse." . $this->treeMenuImagesType;
    $alt_collapse        = "--";
    $img_collapse_corner    = $this->imgwww . "tree_collapse_corner." . $this->treeMenuImagesType;
    $alt_collapse_corner    = "--";
    $img_split        = $this->imgwww . "tree_split." . $this->treeMenuImagesType;
    $alt_split        = "|-";
    $img_corner        = $this->imgwww . "tree_corner." . $this->treeMenuImagesType;
    $alt_corner        = "`-";
    $img_folder_closed    = $this->imgwww . "tree_folder_closed." . $this->treeMenuImagesType;
    $alt_folder_closed    = "->";
    $img_folder_open    = $this->imgwww . "tree_folder_open." . $this->treeMenuImagesType;
    $alt_folder_open    = "->";
    $img_leaf        = $this->imgwww . "tree_leaf." . $this->treeMenuImagesType;
    $alt_leaf        = "->";

    for ($i=$this->_firstItem[$menu_name]; $i<=$this->_lastItem[$menu_name]; $i++) {
        $expand[$i] = 0;
        $visible[$i] = 0;
        $this->tree[$i]["last_item"] = 0;
    }
    for ($i=0; $i<=$this->_maxLevel[$menu_name]; $i++) {
        $levels[$i] = 0;
    }

    // Get numbers of nodes to be expanded
    if ($p != "") {
        $explevels = explode($this->treeMenuSeparator, $p);
        $explevels_count = count($explevels);
        for ($i=0; $i<$explevels_count; $i++) {
            $expand[$explevels[$i]] = 1;
        }
    }

    // Find last nodes of subtrees
    $last_level = $this->_maxLevel;
    for ($i=$this->_lastItem[$menu_name]; $i>=$this->_firstItem[$menu_name]; $i--) {
        if ($this->tree[$i]["level"] < $last_level) {
            for ($j=$this->tree[$i]["level"]+1; $j<=$this->_maxLevel[$menu_name]; $j++) {
                $levels[$j] = 0;
            }
        }
        if ($levels[$this->tree[$i]["level"]] == 0) {
            $levels[$this->tree[$i]["level"]] = 1;
            $this->tree[$i]["last_item"] = 1;
        } else {
            $this->tree[$i]["last_item"] = 0;
        }
        $last_level = $this->tree[$i]["level"];
    }

    // Determine visible nodes
    // all root nodes are always visible
    for ($i=$this->_firstItem[$menu_name]; $i<=$this->_lastItem[$menu_name]; $i++) {
        if ($this->tree[$i]["level"] == 1) {
            $visible[$i] = 1;
        }
    }
    if (isset($explevels)) {
        for ($i=0; $i<$explevels_count; $i++) {
            $n = $explevels[$i];
            if ($n >= $this->_firstItem[$menu_name] && $n <= $this->_lastItem[$menu_name] && $visible[$n] == 1 && $expand[$n] == 1) {
                $j = $n + 1;
                while ($j<=$this->_lastItem[$menu_name] && $this->tree[$j]["level"]>$this->tree[$n]["level"]) {
                    if ($this->tree[$j]["level"] == $this->tree[$n]["level"]+1) {
                        $visible[$j] = 1;
                    }
                    $j++;
                }
            }
        }
    }

    // Output nicely formatted tree
    for ($i=0; $i<$this->_maxLevel[$menu_name]; $i++) {
        $levels[$i] = 1;
    }
    $max_visible_level = 0;
    for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {
        if ($visible[$cnt]) {
            $max_visible_level = max($max_visible_level, $this->tree[$cnt]["level"]);
        }
    }
    for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {
        if ($visible[$cnt]) {
            if ($this->tree[$cnt]["parsed_link"] == "" || $this->tree[$cnt]["parsed_link"] == "#") {
                $this->_phpTreeMenu[$menu_name] .= "<div style=\"display: block; white-space: nowrap;\" class=\"phplmnormal\">\n";
            } else {
                $this->_phpTreeMenu[$menu_name] .= "<div style=\"display: block; white-space: nowrap;\">\n";
            }

            // vertical lines from higher levels
            for ($i=0; $i<$this->tree[$cnt]["level"]-1; $i++) {
                if ($levels[$i] == 1) {
                    $img = $img_vertline;
                    $alt = $alt_vertline;
                } else {
                    $img = $img_space;
                    $alt = $alt_space;
                }
                $this->_phpTreeMenu[$menu_name] .= "<img align=\"top\" border=\"0\" class=\"imgs\" src=\"" . $img . "\" alt=\"" . $alt . "\" />";
            }

            $not_a_leaf = $cnt<$this->_lastItem[$menu_name] && $this->tree[$cnt+1]["level"]>$this->tree[$cnt]["level"];

            if ($not_a_leaf) {
                // Create expand/collapse parameters
                $params = "";
                for ($i=$this->_firstItem[$menu_name]; $i<=$this->_lastItem[$menu_name]; $i++) {
                    if ($expand[$i] == 1 && $cnt!= $i || ($expand[$i] == 0 && $cnt == $i)) {
                        $params .= $this->treeMenuSeparator . $i;
                    }
                }
                if ($params != "") {
                    $params = substr($params, 1);
                }
            }

            if ($this->tree[$cnt]["last_item"] == 1) {
            // corner at end of subtree or t-split
                if ($not_a_leaf) {
                    if ($expand[$cnt] == 0) {
                        $img = $img_expand_corner;
                        $alt = $alt_expand_corner;
                    } else {
                        $img = $img_collapse_corner;
                        $alt = $alt_collapse_corner;
                    }
                    $this->_phpTreeMenu[$menu_name] .= "<a name=\"" . $cnt . "\" class=\"phplm\" href=\"" . $url . $query . $params . "#" . $cnt . "\"><img align=\"top\" border=\"0\" class=\"imgs\" src=\"" . $img . "\" alt=\"" . $alt . "\" /></a>";
                } else {
                    $this->_phpTreeMenu[$menu_name] .= "<img align=\"top\" border=\"0\" class=\"imgs\" src=\"" . $img_corner . "\" alt=\"" . $alt_corner . "\" />";
                }
                $levels[$this->tree[$cnt]["level"]-1] = 0;
            } else {
                if ($not_a_leaf) {
                    if ($expand[$cnt] == 0) {
                        $img = $img_expand;
                        $alt = $alt_expand;
                    } else {
                        $img = $img_collapse;
                        $alt = $alt_collapse;
                    }
                    $this->_phpTreeMenu[$menu_name] .= "<a name=\"" . $cnt . "\" class=\"phplm\" href=\"" . $url . $query . $params . "#" . $cnt . "\"><img align=\"top\" border=\"0\" class=\"imgs\" src=\"" . $img . "\" alt=\"" . $alt . "\" /></a>";
                } else {
                    $this->_phpTreeMenu[$menu_name] .= "<img align=\"top\" border=\"0\" class=\"imgs\" src=\"" . $img_split . "\" alt=\"" . $alt_split . "\" />";
                }
                $levels[$this->tree[$cnt]["level"]-1] = 1;
            }

            if ($this->tree[$cnt]["parsed_link"] == "" || $this->tree[$cnt]["parsed_link"] == "#") {
                $a_href_open = "";
                $a_href_close = "";
            } else {
                $a_href_open = "<a class=\"phplm\" href=\"" . $this->tree[$cnt]["parsed_link"] . "\"" . $this->tree[$cnt]["parsed_title"] . $this->tree[$cnt]["parsed_target"] . ">";
                $a_href_close = "</a>";
            }

            if ($not_a_leaf) {
                if ($expand[$cnt] == 1) {
                    $img = $img_folder_open;
                    $alt = $alt_folder_open;
                } else {
                    $img = $img_folder_closed;
                    $alt = $alt_folder_closed;
                }
                $this->_phpTreeMenu[$menu_name] .= $a_href_open . "<img align=\"top\" border=\"0\" class=\"imgs\" src=\"" . $img . "\" alt=\"" . $alt . "\" />" . $a_href_close;
            } else {
                if ($this->tree[$cnt]["parsed_icon"] != "") {
                    $this->_phpTreeMenu[$menu_name] .= $a_href_open . "<img align=\"top\" border=\"0\" src=\"" . $this->imgwww . $this->tree[$cnt]["parsed_icon"] . "\" width=\"" . $this->tree[$cnt]["iconwidth"] . "\" height=\"" . $this->tree[$cnt]["iconheight"] . "\" alt=\"" . $alt_leaf . "\" />" . $a_href_close;
                } else {
                    $this->_phpTreeMenu[$menu_name] .= $a_href_open . "<img align=\"top\" border=\"0\" class=\"imgs\" src=\"" . $img_leaf . "\" alt=\"" . $alt_leaf . "\" />" . $a_href_close;
                }
            }

            // output item text
            $foobar = $max_visible_level - $this->tree[$cnt]["level"] + 1;
            if ($foobar > 1) {
                $colspan = " colspan=\"" . $foobar . "\"";
            } else {
                $colspan = "";
            }
            $this->_phpTreeMenu[$menu_name] .= "&nbsp;" . $a_href_open . $this->tree[$cnt]["parsed_text"] . $a_href_close . "\n";
            $this->_phpTreeMenu[$menu_name] .= "</div>\n";
        }
    }
/* ********************************************************* */

/*
    // Some (old) browsers do not support the "white-space: nowrap;" CSS property...
    $this->_phpTreeMenu[$menu_name] =
    "<table>\n" .
    "<tr>\n" .
    "<td class=\"phplmnormal\" nowrap=\"nowrap\">\n" .
    $this->_phpTreeMenu[$menu_name] .
    "</td>\n" .
    "</tr>\n" .
    "</table>\n";
*/

    return $this->_phpTreeMenu[$menu_name];
}

/**
* Method that returns the code of the requested PHP Tree Menu
* @param string $menu_name the name of the menu whose PHP Tree Menu code
*   has to be returned
* @return string
*/
function getPHPTreeMenu($menu_name)
{
    return $this->_phpTreeMenu[$menu_name];
}

/**
* Method that prints the code of the requested PHP Tree Menu
* @param string $menu_name the name of the menu whose PHP Tree Menu code
*   has to be printed
* @return void
*/
function printPHPTreeMenu($menu_name)
{
    print $this->_phpTreeMenu[$menu_name];
}

/**
* The method to set the value of separator for the Plain Menu
* @return void
*/
function setPlainMenuSeparator($plainMenuSeparator)
{
    $this->plainMenuSeparator = $plainMenuSeparator;
}

/**
* The method to set plainMenuTpl
* @return boolean
*/
function setPlainMenuTpl($plainMenuTpl)
{
    if (str_replace("/", "", $plainMenuTpl) == $plainMenuTpl) {
        $plainMenuTpl = $this->tpldir . $plainMenuTpl;
    }
    if (!file_exists($plainMenuTpl)) {
        $this->error("setPlainMenuTpl: file $plainMenuTpl does not exist.");
        return false;
    }
    $this->plainMenuTpl = $plainMenuTpl;
    return true;
}

/**
* Method to prepare a new Plain Menu.
*
* This method processes items of a menu to prepare and return
* the corresponding Plain Menu code.
*
* @param string $menu_name the name of the menu whose items have to be processed
* @return string
*/
function newPlainMenu(
    $menu_name = ""    // non consistent default...
    ) {
    $plain_menu_blck = "";
    $t = new LayersTemplate();
    $t->setFile("tplfile", $this->plainMenuTpl);
    $t->setBlock("tplfile", "template", "template_blck");
    $t->setBlock("template", "plain_menu_cell", "plain_menu_cell_blck");
    $t->setVar("plain_menu_cell_blck", "");
    for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {
        $nbsp = "";
        for ($i=1; $i<$this->tree[$cnt]["level"]; $i++) {
            $nbsp .= "&nbsp;&nbsp;&nbsp;";
        }
        $t->setVar(array(
            "nbsp"        => $nbsp,
            "link"        => $this->tree[$cnt]["parsed_link"],
            "title"        => $this->tree[$cnt]["parsed_title"],
            "target"    => $this->tree[$cnt]["parsed_target"],
            "text"        => $this->tree[$cnt]["parsed_text"]
        ));
        $plain_menu_blck .= $t->parse("plain_menu_cell_blck", "plain_menu_cell", false);
    }
    $t->setVar("plain_menu_cell_blck", $plain_menu_blck);
    $this->_plainMenu[$menu_name] = $t->parse("template_blck", "template");

    return $this->_plainMenu[$menu_name];
}

/**
* Method that returns the code of the requested Plain Menu
* @param string $menu_name the name of the menu whose Plain Menu code
*   has to be returned
* @return string
*/
function getPlainMenu($menu_name)
{
    return $this->_plainMenu[$menu_name];
}

/**
* Method that prints the code of the requested Plain Menu
* @param string $menu_name the name of the menu whose Plain Menu code
*   has to be printed
* @return void
*/
function printPlainMenu($menu_name)
{
    print $this->_plainMenu[$menu_name];
}

/**
* The method to set the value of separator for the Horizontal Plain Menu
* @return void
*/
function setHorizontalPlainMenuSeparator($horizontalPlainMenuSeparator)
{
    $this->horizontalPlainMenuSeparator = $horizontalPlainMenuSeparator;
}

/**
* The method to set horizontalPlainMenuTpl
* @return boolean
*/
function setHorizontalPlainMenuTpl($horizontalPlainMenuTpl)
{
    if (str_replace("/", "", $horizontalPlainMenuTpl) == $horizontalPlainMenuTpl) {
        $horizontalPlainMenuTpl = $this->tpldir . $horizontalPlainMenuTpl;
    }
    if (!file_exists($horizontalPlainMenuTpl)) {
        $this->error("setHorizontalPlainMenuTpl: file $horizontalPlainMenuTpl does not exist.");
        return false;
    }
    $this->horizontalPlainMenuTpl = $horizontalPlainMenuTpl;
    return true;
}

/**
* Method to prepare a new Horizontal Plain Menu.
*
* This method processes items of a menu to prepare and return
* the corresponding Horizontal Plain Menu code.
*
* @param string $menu_name the name of the menu whose items have to be processed
* @return string
*/
function newHorizontalPlainMenu(
    $menu_name = ""    // non consistent default...
    ) {
    $horizontal_plain_menu_blck = "";
    $t = new LayersTemplate();
    $t->setFile("tplfile", $this->horizontalPlainMenuTpl);
    $t->setBlock("tplfile", "template", "template_blck");
    $t->setBlock("template", "horizontal_plain_menu_cell", "horizontal_plain_menu_cell_blck");
    $t->setVar("horizontal_plain_menu_cell_blck", "");
    $t->setBlock("horizontal_plain_menu_cell", "plain_menu_cell", "plain_menu_cell_blck");
    $t->setVar("plain_menu_cell_blck", "");
    for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {
        if ($this->tree[$cnt]["level"] == 1 && $cnt > $this->_firstItem[$menu_name]) {
            $t->parse("horizontal_plain_menu_cell_blck", "horizontal_plain_menu_cell", true);
            $t->setVar("plain_menu_cell_blck", "");
        }
        $nbsp = "";
        for ($i=1; $i<$this->tree[$cnt]["level"]; $i++) {
            $nbsp .= "&nbsp;&nbsp;&nbsp;";
        }
        $t->setVar(array(
            "nbsp"        => $nbsp,
            "link"        => $this->tree[$cnt]["parsed_link"],
            "title"        => $this->tree[$cnt]["parsed_title"],
            "target"    => $this->tree[$cnt]["parsed_target"],
            "text"        => $this->tree[$cnt]["parsed_text"]
        ));
        $t->parse("plain_menu_cell_blck", "plain_menu_cell", true);
    }
    $t->parse("horizontal_plain_menu_cell_blck", "horizontal_plain_menu_cell", true);
    $this->_horizontalPlainMenu[$menu_name] = $t->parse("template_blck", "template");

    return $this->_horizontalPlainMenu[$menu_name];
}

/**
* Method that returns the code of the requested Horizontal Plain Menu
* @param string $menu_name the name of the menu whose Horizontal Plain Menu code
*   has to be returned
* @return string
*/
function getHorizontalPlainMenu($menu_name)
{
    return $this->_horizontalPlainMenu[$menu_name];
}

/**
* Method that prints the code of the requested Horizontal Plain Menu
* @param string $menu_name the name of the menu whose Horizontal Plain Menu code
*   has to be printed
* @return void
*/
function printHorizontalPlainMenu($menu_name)
{
    print $this->_horizontalPlainMenu[$menu_name];
}

} /* END OF CLASS */
