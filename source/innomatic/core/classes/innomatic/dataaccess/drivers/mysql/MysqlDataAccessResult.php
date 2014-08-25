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
 * @license    http://www.innomaticplatform.com/license/ New BSD License
 * @link       http://www.innomaticplatform.com
 */
namespace Innomatic\Dataaccess\Drivers\Mysql;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class MysqlDataAccessResult extends \Innomatic\Dataaccess\DataAccessResult
{

    public $suppseek = true;

    protected $start = false;

    public function __construct(&$resultid)
    {
        $this->supp['seek'] = true;
        parent::__construct($resultid);
    }

    protected function init()
    {
        $this->resultrows = $this->resultid->num_rows;
        $this->resultfields = $this->resultid->field_count;
    }

    protected function seek($row)
    {
        $this->resultid->data_seek($row);
        $this->currentrow = $row;
        return true;
    }

    protected function fetch()
    {
        $this->currfields = $this->resultid->fetch_array();
        return ($this->currfields == true);
    }

    protected function doFree()
    {
        return $this->resultid->free_result();
    }
}
