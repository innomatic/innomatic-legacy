<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

/*!
 @class Permissions

 @abstract Administration interface pages permissions handling
 */
class Permissions
{
    public $db;
    public $gid;
    public $permds;
    const NODETYPE_GROUP = 'group';
    const NODETYPE_PAGE = 'page';
    const NODE_FULLYENABLED = 1;
    const NODE_PARTIALLYENABLED = 2;
    const NODE_NOTENABLED = 3;

    public function Permissions(DataAccess $domainda, $gid)
    {
        $this->db = $domainda;
        $this->gid = $gid;
    }

    // Enable a node
    public function enable($node, $ntype)
    {
        $this->db->execute('DELETE FROM domain_users_permissions '."WHERE groupid = '".$this->gid."' "."AND permnode = '".$ntype.$node."'");

        if (strcmp($ntype, 'group') == 0) {
            $apquery = $this->db->execute('SELECT id FROM domain_panels WHERE groupid = '.$this->db->formatText($node));
            while (!$apquery->eof) {
                $this->db->execute('DELETE FROM domain_users_permissions '."WHERE groupid = '".$this->gid."' "."AND permnode = 'page".$apquery->getFields('id')."'");
                $apquery->moveNext();
            }
            $apquery->free();
        }
    }

    // Disable a node
    public function disable($node, $ntype)
    {
        if ($this->check($node, $ntype) != Permissions::NODE_NOTENABLED) {
            $this->db->execute("INSERT into domain_users_permissions values ( '".$this->gid."','".$ntype.$node."')");

            if (strcmp($ntype, Permissions::NODETYPE_GROUP) == 0) {
                $apquery = $this->db->execute("SELECT id FROM domain_panels WHERE groupid = '".$node."'");
                while (!$apquery->eof) {
                    $this->disable($apquery->getFields('id'), 'page');
                    $apquery->moveNext();
                }
                $apquery->free();
            }
        }
    }

    // Check a node permission status
    public function check($node, $ntype)
    {
        $result = Permissions::NODE_NOTENABLED;

        $pquery = $this->db->execute('SELECT groupid FROM domain_users_permissions WHERE groupid = '.$this->gid.' AND permnode = '.$this->db->formatText($ntype.$node));

        if ($pquery->getNumberRows() == 0)
            $result = Permissions::NODE_FULLYENABLED;

        if (strcmp($ntype, Permissions::NODETYPE_GROUP) == 0) {
            $apquery = $this->db->execute('SELECT id FROM domain_panels WHERE groupid = '.$node);
            $pages = 0;

            while (!$apquery->eof) {
                if ($this->check($apquery->getFields('id'), Permissions::NODETYPE_PAGE) == Permissions::NODE_FULLYENABLED) {
                    $result = Permissions::NODE_PARTIALLYENABLED;
                    $pages ++;
                }
                $apquery->moveNext();
            }

            if (($apquery->getNumberRows() == $pages) and ($apquery->getNumberRows() != 0))
                $result = Permissions::NODE_FULLYENABLED;

            $apquery->free();
        }
        $pquery->free();

        return $result;
    }

    // Removes every permission referred to a certain node
    // for every group
    public function removeNodes($node, $type)
    {
        //return &$this->db->execute( "DELETE FROM domain_users_permissions WHERE permnode = '".$type.$node."'" );
    }

    // Gets node id of a page by its filename
    //
    public function getNodeIdFromFileName($filename)
    {
        $filename = basename($filename);

        if (!empty($filename) and $this->db) {
            $query = $this->db->execute('SELECT id FROM domain_panels WHERE name = '.$this->db->formatText($filename));
            if ($query->getNumberRows())
                return $query->getFields('id');
        }
        return false;
    }
}
