<?php

class PusherBatchPushTest extends PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        if (PUSHERAPP_AUTHKEY === '' || PUSHERAPP_SECRET === '' || PUSHERAPP_APPID === '') {
            $this->markTestSkipped('Please set the
            PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET and
            PUSHERAPP_APPID keys.');
        } else {
            $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, false, PUSHERAPP_HOST);
            $this->pusher->setLogger(new TestLogger());
        }
    }

    public function testObjectConstruct()
    {
        $this->assertNotNull($this->pusher, 'Created new Pusher\Pusher object');
    }

    public function testSimplePush()
    {
        $batch = array();
        $batch[] = array('channel' => 'test_channel', 'name' => 'my_event', 'data' => array('my' => 'data'));
        $string_trigger = $this->pusher->triggerBatch($batch);
        $this->assertTrue($string_trigger, 'Trigger with string payload');
    }

    public function testTLSPush()
    {
        $options = array(
            'useTLS' => true,
            'host'   => PUSHERAPP_HOST,
        );
        $pc = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
        $pc->setLogger(new TestLogger());

        $batch = array();
        $batch[] = array('channel' => 'test_channel', 'name' => 'my_event', 'data' => array('my' => 'data'));
        $string_trigger = $pc->triggerBatch($batch);
        $this->assertTrue($string_trigger, 'Trigger with string payload');
    }

    public function testTriggerBatchNonEncryptedEventsWithObjectPayloads()
    {
        $options = array(
            'useTLS' => true,
            'host'   => PUSHERAPP_HOST,
        );
        $pc = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
        $pc->setLogger(new TestLogger());

        $batch = array();
        $batch[] = array('channel' => 'test_channel', 'name' => 'my_event', 'data' => array('my' => 'data'));
        $batch[] = array('channel' => 'mio_canale', 'name' => 'my_event2', 'data' => array('my' => 'data2'));
        $string_trigger = $pc->triggerBatch($batch);
        $this->assertTrue($string_trigger, 'Failed to triggerBatch Multiple Events');
    }

    public function testTriggerBatchWithSingleEvent()
    {
        $options = array(
            'useTLS' => true,
            'host'   => PUSHERAPP_HOST,
        );
        $pc = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
        $pc->setLogger(new TestLogger());

        $batch = array();
        $batch[] = array('channel' => 'test_channel', 'name' => 'my_event', 'data' => 'test-string');
        $string_trigger = $pc->triggerBatch($batch);
        $this->assertTrue($string_trigger, 'Failed to triggerBatch Multiple Events');
    }

    public function testTriggerBatchWithMultipleNonEncryptedEventsWithStringPayloads()
    {
        $options = array(
            'useTLS' => true,
            'host'   => PUSHERAPP_HOST,
        );
        $pc = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
        $pc->setLogger(new TestLogger());

        $batch = array();
        $batch[] = array('channel' => 'test_channel', 'name' => 'my_event', 'data' => 'test-string');
        $batch[] = array('channel' => 'test_channel2', 'name' => 'my_event2', 'data' => 'test-string2');
        $string_trigger = $pc->triggerBatch($batch);
        $this->assertTrue($string_trigger, 'Failed to triggerBatch Multiple Events');
    }

    public function testTriggerBatchWithMultipleCombinationsofStringAndObjectPayloads()
    {
        $options = array(
            'useTLS' => true,
            'host'   => PUSHERAPP_HOST,
        );
        $pc = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
        $pc->setLogger(new TestLogger());

        $batch = array();
        $batch[] = array('channel' => 'test_channel', 'name' => 'my_event', 'data' => 'test-string');
        $batch[] = array('channel' => 'test_channel2', 'name' => 'my_event2', 'data' => array('my' => 'data2'));
        $string_trigger = $pc->triggerBatch($batch);
        $this->assertTrue($string_trigger, 'Failed to triggerBatch Multiple Events');
    }

    public function testTriggerBatchWithWithEncryptedEventSuccess()
    {
        $options = array(
            'useTLS'                => true,
            'host'                  => PUSHERAPP_HOST,
            'encryption_master_key' => 'cAzRH3W9FZM3iXqSNIGtKztwNuCz9xMV',
        );
        $pc = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
        $pc->setLogger(new TestLogger());

        $batch = array();
        $batch[] = array('channel' => 'private-encrypted-test_channel', 'name' => 'my_event', 'data' => 'test-string');
        $string_trigger = $pc->triggerBatch($batch);
        $this->assertTrue($string_trigger, 'Failed to triggerBatch Multiple Events');
    }

    public function testTriggerBatchWithMultipleEncryptedEventsSuccess()
    {
        $options = array(
            'useTLS'                => true,
            'host'                  => PUSHERAPP_HOST,
            'encryption_master_key' => 'cAzRH3W9FZM3iXqSNIGtKztwNuCz9xMV',
        );
        $pc = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
        $pc->setLogger(new TestLogger());

        $batch = array();
        $batch[] = array('channel' => 'test_channel', 'name' => 'my_event', 'data' => 'test-string');
        $batch[] = array('channel' => 'private-encrypted-test_channel2', 'name' => 'my_event2', 'data' => 'test-string2');
        $string_trigger = $pc->triggerBatch($batch);
        $this->assertTrue($string_trigger, 'Failed to triggerBatch Multiple Events');
    }

    public function testTriggerBatchWithMultipleCombinationsofStringsAndObjectsWithEncryptedEventSuccess()
    {
        $options = array(
            'useTLS'                => true,
            'host'                  => PUSHERAPP_HOST,
            'encryption_master_key' => 'cAzRH3W9FZM3iXqSNIGtKztwNuCz9xMV',
        );
        $pc = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
        $pc->setLogger(new TestLogger());

        $batch = array();
        $batch[] = array('channel' => 'test_channel', 'name' => 'my_event', 'data' => 'secret-string');
        $batch[] = array('channel' => 'private-encrypted-test_channel2', 'name' => 'my_event2', 'data' => array('my' => 'data2'));
        $string_trigger = $pc->triggerBatch($batch);
        $this->assertTrue($string_trigger, 'Failed to triggerBatch Multiple Events');
    }

    /**
     * @expectedException Error
     */
    public function testTriggerBatchMultipleEventsWithEncryptedEventWithoutEncryptionMasterKeyError()
    {
        $options = array(
            'useTLS' => true,
            'host'   => PUSHERAPP_HOST,
        );
        $pc = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
        $pc->setLogger(new TestLogger());

        $batch = array();
        $batch[] = array('channel' => 'my_test_chan', 'name' => 'my_event', 'data' => array('my' => 'data'));
        $batch[] = array('channel' => 'private-encrypted-ceppaio', 'name' => 'my_private_encrypted_event', 'data' => array('my' => 'to_be_encrypted_data_shhhht'));
        $string_trigger = $pc->triggerBatch($batch);
        $this->assertTrue($string_trigger, 'Failed to triggerBatch Multiple Events');
    }

    public function testTriggerBatchWithMultipleEncryptedEventsWithEncryptionMasterKeySuccess()
    {
        $options = array(
            'useTLS'                => true,
            'host'                  => PUSHERAPP_HOST,
            'encryption_master_key' => 'cAzRH3W9FZM3iXqSNIGtKztwNuCz9xMV',
        );
        $pc = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
        $pc->setLogger(new TestLogger());

        $batch = array();
        $batch[] = array('channel' => 'my_test_chan', 'name' => 'my_event', 'data' => array('my' => 'data'));
        $batch[] = array('channel' => 'private-encrypted-ceppaio', 'name' => 'my_private_encrypted_event', 'data' => array('my' => 'to_be_encrypted_data_shhhht'));
        $string_trigger = $pc->triggerBatch($batch);
        $this->assertTrue($string_trigger, 'Failed to triggerBatch Multiple Events');
    }

    public function testSendingOver10kBMessageReturns413()
    {
        $data = str_pad('', 11 * 1024, 'a');
        $batch = array();
        $batch[] = array('channel' => 'test_channel', 'name' => 'my_event', 'data' => $data);
        $response = $this->pusher->triggerBatch($batch, true, true);
        $this->assertContains('content of this event', $response['body']);
        $this->assertEquals(413, $response['status'], '413 HTTP status response expected');
    }

    public function testSendingOver10messagesReturns400()
    {
        $batch = array();
        foreach (range(1, 11) as $i) {
            $batch[] = array('channel' => 'test_channel', 'name' => 'my_event', 'data' => array('index' => $i));
        }
        $response = $this->pusher->triggerBatch($batch, true, false);
        $this->assertContains('Batch too large', $response['body']);
        $this->assertEquals(400, $response['status'], '400 HTTP status response expected');
    }
}
