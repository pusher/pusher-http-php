<?php namespace Pusher;

class HelperTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateStringFromArray()
    {
        $data = array(
            'hello' => 'Pusher',
            'world' => array('table','keyboard')
        );

        $this->assertEquals('hello&Pusher=world&table,keyboard', Helper::arrayToString('&', '=', $data));
    }
}