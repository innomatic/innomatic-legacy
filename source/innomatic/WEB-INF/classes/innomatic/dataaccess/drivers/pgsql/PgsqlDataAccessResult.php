<?php
/**
 * Innomatic
 *
 * LICENSE 
 * 
 * This source file is subject to the new BSD license that is bundled 
 * with this package in the file LICENSE.
 *
 * @copyright  1999-2012 Innoteam S.r.l.
 * @license    http://www.innomatic.org/license/   BSD License
 * @link       http://www.innomatic.org
 * @since      Class available since Release 5.0
*/

require_once('innomatic/dataaccess/DataAccessResult.php');

class PgsqlDataAccessResult extends DataAccessResult {
    var $suppseek = true;
    var $_start = false;

    public function __construct(&$resultid) {
        $this->supp['seek'] = true;
        parent::__construct($resultid);
    }

    protected function init() {
        $this->resultrows = @pg_num_rows($this->resultid);
        $this->resultfields = @pg_num_fields($this->resultid);
    }

    protected function seek($row) {
        $this->currentrow = $row;
        return true;
    }

    protected function fetch() {
        $this->currfields = @pg_fetch_array($this->resultid, $this->currentrow);
        return ($this->currfields == true);
    }

    protected function doFree() {
        return @pg_free_result($this->resultid);
    }
}
