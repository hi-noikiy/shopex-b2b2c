<?php
class notifyMatrix extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
    }

    public function testListener()
    {
        $listener = kernel::single('sysuser_events_listeners_notifyShopexMatrix');

        $listener->createUser(1);

    }
}
