<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innoteam Srl
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/
namespace Innomatic\Dataaccess\Drivers\Mysql;

class MysqlDataAccessResult extends \Innomatic\Dataaccess\DataAccessResult
{
    public $suppseek = true;
    private $_start = false;

    public function __construct(&$resultid)
    {
        $this->supp['seek'] = true;
        parent::__construct($resultid);
    }

    protected function init()
    {
        $this->resultrows = @mysql_num_rows($this->resultid);
        $this->resultfields = @mysql_num_fields($this->resultid);
    }

    protected function seek($row)
    {
        @mysql_data_seek($this->resultid, $row);
        $this->currentrow = $row;
        return true;
    }

    protected function fetch()
    {
        $this->currfields = @mysql_fetch_array($this->resultid);
        return ($this->currfields == true);
    }

    protected function doFree()
    {
        return @mysql_free_result($this->resultid);
    }
}
