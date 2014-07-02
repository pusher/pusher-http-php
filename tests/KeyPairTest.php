<?php

namespace pusher\Tests;

use pusher\KeyPair;

/**
 * @covers pusher\KeyPair
 */
class KeyPairTest extends \PHPUnit_Framework_TestCase {
    /**
     * @before
     */
    public function keypair() {
        $this->kp = new KeyPair('foo', 'bar');
    }

    public function testSign() {
        $signature = $this->kp->sign('ahoi');
        $this->assertEquals('a66ed82a8fe9d4534399025aea6784cab7f7da21064c122b49ca63037af1f584', $signature);
    }

    public function testVerify() {
        $signature = $this->kp->sign('ahoi');
        $this->assertTrue($this->kp->verify($signature, 'ahoi'));
        $this->assertFalse($this->kp->verify($signature, 'ahoi2'));
    }

    public function testAuthenticate() {
        $signature = $this->kp->authenticate('a', 'b');
        $this->assertEquals('5f1fd0c2612847d85b717d6cd9fca80d04ddd77b8bace2e5863998aac14b1eeb', $signature);
    }

}
