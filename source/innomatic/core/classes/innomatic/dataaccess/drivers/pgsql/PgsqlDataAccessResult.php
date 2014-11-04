<?php
/**
 * Innomatic
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2014 Innomatic Company
 * @license    http://www.innomatic.io/license/ New BSD License
 * @link       http://www.innomatic.io
*/
namespace Innomatic\Dataaccess\Drivers\Pgsql;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class PgsqlDataAccessResult extends \Innomatic\Dataaccess\DataAccessResult
{
    public $suppseek = true;
    public $_start = false;

    public function __construct(&$resultid)
    {
        $this->supp['seek'] = true;
        parent::__construct($resultid);
    }

    protected function init()
    {
        $this->resultrows = @pg_num_rows($this->resultid);
        $this->resultfields = @pg_num_fields($this->resultid);
    }

    protected function seek($row)
    {
        $this->currentrow = $row;
        return true;
    }

    protected function fetch()
    {
        $this->currfields = @pg_fetch_array($this->resultid, $this->currentrow);
        return ($this->currfields == true);
    }

    protected function doFree()
    {
        return @pg_free_result($this->resultid);
    }
}
