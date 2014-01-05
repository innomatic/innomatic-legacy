<?php

class CarModule extends \Innomatic\Module\ModulePersistentObject
{
    public function moduleRun()
    {
        return 'Manufacturer: '.$this->valueObject->getValue('manufacturer').', model = '.$this->valueObject->getValue('model');
    }

    public function deploy()
    {
        //$this->dataAccess->execute('CREATE TABLE cars(id INTEGER PRIMARY KEY, manufacturer CHAR(255), model CHAR(255))');
    }

    public function undeploy()
    {
        //$this->dataAccess->execute('DROP TABLE cars');
        //$this->dataAccess->close();
    }

    public function redeploy()
    {
    }
}
