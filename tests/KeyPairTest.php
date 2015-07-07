<?php

namespace Pusher\Tests;

use Pusher\KeyPair;

/**
 * @covers pusher\KeyPair
 */
class KeyPairTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @before
     */
    public function keypair()
    {
        $this->kp = new KeyPair('foo', 'bar');
    }

    public function testSign()
    {
        $signature = $this->kp->sign('ahoi');
        $this->assertEquals('a66ed82a8fe9d4534399025aea6784cab7f7da21064c122b49ca63037af1f584', $signature);
    }

    public function testVerify()
    {
        $signature = $this->kp->sign('ahoi');
        $this->assertTrue($this->kp->verify($signature, 'ahoi'));
        $this->assertFalse($this->kp->verify($signature, 'ahoi2'));
    }

    public function testAuthenticate()
    {
        $signature = $this->kp->authenticate('38087.11062758', 'private-messages');
        $this->assertEquals('413ad362eff8e6d4688b64f91ed99b31faa442ff54ba0048755da649219540a1', $signature);
    }
}
