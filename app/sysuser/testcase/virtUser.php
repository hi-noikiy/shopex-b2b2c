<?php
class virtUser extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {

    }

    public function testGet()
    {
        $model = app::get('sysuser')->model('userVirt');
        unset($model->app);

        var_dump($model);
    }

}
