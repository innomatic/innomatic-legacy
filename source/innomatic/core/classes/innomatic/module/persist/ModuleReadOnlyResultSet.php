<?php
namespace Innomatic\Module\Persist;

/**
 * @author Alex Pagnoni <alex.pagnoni@innoteam.it>
 * @copyright Copyright 2004-2014 Innoteam Srl
 * @since 5.1
 */
class ModuleReadOnlyResultSet
{
    private $resultSet;

    public function __construct(\Innomatic\Dataaccess\DataAccessResult $resultSet)
    {
        $this->resultSet = $resultSet;
    }

    public function getNext(\Innomatic\Module\ModuleObject $businessObject)
    {
        $row = $this->resultSet->getFields();

        $class = new \ReflectionObject($businessObject->moduleGetVO());
        $properties = $class->getProperties();

        for ($i = 0; $i < count($properties); $i ++) {
            $prop_name = $properties[$i]->getName();
            $businessObject->moduleGetVO()->setValue($prop_name, $row[$prop_name]);
        }

        $this->resultSet->moveNext();
        return $businessObject;
    }

    public function hasNext()
    {
        return $this->resultSet->hasNext();
    }

    public function rowCount()
    {
        return $this->resultSet->getNumberRows();
    }

    public function eof()
    {
        return $this->resultSet->eof;
    }
}
