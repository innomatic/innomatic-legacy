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
*/
namespace Innomatic\Dataaccess;

/**
 * @since 5.0.0 introduced
 * @author Alex Pagnoni <alex.pagnoni@innomatic.io>
 */
class DataAccessXmlTable extends \Innomatic\Xml\XMLParser
{
    /*! @var mLog log handler */
    public $mLog;
    /*! @var deffile definition file to parse */
    public $deffile;
    public $mData;
    public $sql = array();
    public $temp_sql;
    public $mFields = array();
    public $mFieldsList = array();
    public $mTableStructure = array();
    public $insfields = array();
    public $values = array();
    public $table_options;
    public $db;
    public $mAction;
    const SQL_CREATE = 1;
    const SQL_DROP = 2;
    const SQL_UPDATE_OLD = 3;
    const SQL_UPDATE_NEW = 4;

    /*!
     @function __construct

     @abstract Class constructor.
     */
    public function __construct(\Innomatic\Dataaccess\DataAccess $db, $act)
    {
        parent::__construct();
        $this->mAction = $act;
        $this->db = $db;
    }


    public function load_deffile($deffile)
    {
        $result = false;
        $this->deffile = $deffile;
        $content = file_get_contents( $this->deffile );
        if ( $content ) {
            $this->get_data( $content );
            $result = true;
        }

        return $result;
    }

    public function get_data($data)
    {
        $this->mData = $data;
    }

    public function doTagOpen($tag, $attrs)
    {
        switch ( $tag ) {
        case 'TABLE':
            switch ( $this->mAction ) {
            case DataAccessXmlTable::SQL_CREATE:
                if ( isset($attrs['TEMPORARY']   ) and $attrs['TEMPORARY']   == 1  ) $temporary = 'TEMPORARY '; else $temporary = '';
                if ( isset($attrs['IFNOTEXISTS'] ) and $attrs['IFNOTEXISTS'] == 1  ) $ifnotexists = 'IF NOT EXISTS '; else $ifnotexists = '';
                if ( isset($attrs['OPTIONS']     ) and $attrs['OPTIONS']     != '' ) $this->table_options = ' '.$attrs['OPTIONS']; else $this->table_options = '';
                $this->temp_sql = 'CREATE '.$temporary.'TABLE '.$ifnotexists.$attrs['NAME'].' ( ';
                break;

            case DataAccessXmlTable::SQL_DROP:
                $this->sql[] = 'DROP TABLE '.$attrs['NAME'].';';
                break;

            case DataAccessXmlTable::SQL_UPDATE_NEW:
            case DataAccessXmlTable::SQL_UPDATE_OLD:
                break;
            }
            break;

        case 'FIELD':
            switch ( $this->mAction ) {
            case DataAccessXmlTable::SQL_CREATE:
            case DataAccessXmlTable::SQL_DROP:
            case DataAccessXmlTable::SQL_UPDATE_NEW:
                $attrs['name']    = isset($attrs['NAME'] ) ? $attrs['NAME'] : '';
                $attrs['type']    = isset($attrs['TYPE'] ) ? $attrs['TYPE'] : '';

                if ( isset($attrs['DEFAULT'] ) ) $attrs['default'] = isset($attrs['DEFAULT'] ) ? $attrs['DEFAULT'] : '';
                if ( isset($attrs['NOTNULL'] ) ) $attrs['notnull'] = isset($attrs['NOTNULL'] ) ? $attrs['NOTNULL'] : '';
                if ( isset($attrs['LENGTH'] ) ) $attrs['length']  = isset($attrs['LENGTH'] ) ? $attrs['LENGTH'] : '';
                $this->mFields[$attrs['name']]   = $this->db->getFieldTypeDeclaration( $attrs['NAME'], $attrs );
                $this->mFieldsList[$attrs['name']] = $attrs['name'];
                $this->mTableStructure[$attrs['name']] = $attrs;
                break;

            case DataAccessXmlTable::SQL_UPDATE_OLD:
                $this->mFieldsList[$attrs['NAME']] = $attrs['NAME'];
                break;
            }
            break;

        case 'KEY':
            switch ( $this->mAction ) {
            case DataAccessXmlTable::SQL_CREATE:
            case DataAccessXmlTable::SQL_DROP:
                if ( isset($attrs['TYPE'] ) and $attrs['TYPE'] == 'primary' ) $this->mFields[] = 'PRIMARY KEY ('.$attrs['FIELD'].')';
                if ( isset($attrs['TYPE'] ) and $attrs['TYPE'] == 'unique'  ) $this->mFields[] = 'UNIQUE ('.$attrs['FIELD'].')';
                if ( isset($attrs['TYPE'] ) and $attrs['TYPE'] == 'index'   ) $this->mFields[] = 'INDEX ('.$attrs['FIELD'].')';
                break;

            case DataAccessXmlTable::SQL_UPDATE_NEW:
            case DataAccessXmlTable::SQL_UPDATE_OLD:
                break;
            }
            break;

        case 'SEQUENCE':
            switch ( $this->mAction ) {
            case DataAccessXmlTable::SQL_CREATE:
            case DataAccessXmlTable::SQL_DROP:
                $attrs['name']  = $attrs['NAME'];
                $attrs['start'] = isset($attrs['START'] ) ? $attrs['START'] : '';
                if ( $attrs['start'] == '' ) $attrs['start'] = 1;
                $this->sql[] = $this->mAction == DataAccessXmlTable::SQL_CREATE ? $this->db->getCreateSequenceQuery( $attrs ) : $this->db->getDropSequenceQuery( $attrs );
                break;

            case DataAccessXmlTable::SQL_UPDATE_NEW:
            case DataAccessXmlTable::SQL_UPDATE_OLD:
                break;
            }
            break;

        case 'INSERT':
            switch ( $this->mAction ) {
            case DataAccessXmlTable::SQL_CREATE:
                $this->temp_sql = 'INSERT INTO '.$attrs['TABLE'].' ';
                break;

            case DataAccessXmlTable::SQL_DROP:
            case DataAccessXmlTable::SQL_UPDATE_NEW:
            case DataAccessXmlTable::SQL_UPDATE_OLD:
                break;
            }
            break;

        case 'DATA':
            $this->insfields[] = $attrs['FIELD'];
            $this->values[]    = $this->db->formatText( $attrs['VALUE'] );
            break;
        }
    }

    public function doTagClose($tag)
    {
        switch ( $tag ) {
        case 'TABLE':
            switch ( $this->mAction ) {
            case DataAccessXmlTable::SQL_CREATE:
                $this->sql[] = $this->temp_sql.implode( ', ', $this->mFields ).' )'.$this->table_options.' CHARACTER SET utf8 COLLATE utf8_general_ci;';
                $this->temp_sql = '';
                $this->mFields = array();
                break;

            case DataAccessXmlTable::SQL_DROP:
            case DataAccessXmlTable::SQL_UPDATE_NEW:
            case DataAccessXmlTable::SQL_UPDATE_OLD:
                break;
            }
            break;

        case 'INSERT':
            switch ( $this->mAction ) {
            case DataAccessXmlTable::SQL_CREATE:
                $this->sql[] = $this->temp_sql.' ( '.implode( ', ', $this->insfields ).' ) VALUES ( ';
                $this->sql[] = implode( ', ', $this->values ).' );';
                $this->insfields = array();
                $this->values    = array();
                break;

            case DataAccessXmlTable::SQL_DROP:
            case DataAccessXmlTable::SQL_UPDATE_NEW:
            case DataAccessXmlTable::SQL_UPDATE_OLD:
                break;
            }
            break;
        }
    }

    public function doCdata($data)
    {
    }

    public function getSQL()
    {
        $ret = '';
        $this->parse( $this->mData );

        for ( $i = 0; $i < count( $this->sql ); $i++ ) $ret .= $this->sql[$i];
        return $ret;
    }
}
