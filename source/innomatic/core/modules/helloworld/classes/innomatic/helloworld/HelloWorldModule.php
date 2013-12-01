<?php

require_once('innomatic/module/ModuleObject.php');

class HelloWorldModule extends ModuleObject
{
    public function moduleHelloWorld()
    {
        return 'Hello World!';
    }

    public function deploy()
    {
    }

    public function undeploy()
    {
    }

    public function redeploy()
    {
    }
}
