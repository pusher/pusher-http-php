<?php

class PusherBatchPushTest extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        if (PUSHERAPP_AUTHKEY === '' || PUSHERAPP_SECRET === '' || PUSHERAPP_APPID === '') {
            $this->markTestSkipped('Please set the
            PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET and
            PUSHERAPP_APPID keys.');
        } else {
            $this->pusher = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, array(), PUSHERAPP_HOST);
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
        $result = $this->pusher->triggerBatch($batch);
        $this->assertEquals(new stdClass(), $result);
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
        $result = $pc->triggerBatch($batch);
        $this->assertEquals(new stdClass(), $result);
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
        $result = $pc->triggerBatch($batch);
        $this->assertEquals(new stdClass(), $result);
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
        $result = $pc->triggerBatch($batch);
        $this->assertEquals(new stdClass(), $result);
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
        $result = $pc->triggerBatch($batch);
        $this->assertEquals(new stdClass(), $result);
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
        $result = $pc->triggerBatch($batch);
        $this->assertEquals(new stdClass(), $result);
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
        $result = $pc->triggerBatch($batch);
        $this->assertEquals(new stdClass(), $result);
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
        $result = $pc->triggerBatch($batch);
        $this->assertEquals(new stdClass(), $result);
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
        $result = $pc->triggerBatch($batch);
        $this->assertEquals(new stdClass(), $result);
    }

    public function testTriggerBatchMultipleEventsWithEncryptedEventWithoutEncryptionMasterKeyError()
    {
        $this->expectException(Error::class);

        $options = array(
            'useTLS' => true,
            'host'   => PUSHERAPP_HOST,
        );
        $pc = new Pusher\Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);
        $pc->setLogger(new TestLogger());

        $batch = array();
        $batch[] = array('channel' => 'my_test_chan', 'name' => 'my_event', 'data' => array('my' => 'data'));
        $batch[] = array('channel' => 'private-encrypted-ceppaio', 'name' => 'my_private_encrypted_event', 'data' => array('my' => 'to_be_encrypted_data_shhhht'));
        $pc->triggerBatch($batch);
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
        $result = $pc->triggerBatch($batch);
        $this->assertEquals(new stdClass(), $result);
    }

    public function testSendingOver10kBMessageReturns413()
    {
        $this->expectException(\Pusher\ApiErrorException::class);
        $this->expectExceptionMessage('content of this event');
        $this->expectExceptionCode('413');

        $data = str_pad('', 11 * 1024, 'a');
        $batch = array();
        $batch[] = array('channel' => 'test_channel', 'name' => 'my_event', 'data' => $data);
        $this->pusher->triggerBatch($batch, true, true);
    }

    public function testSendingOver10messagesReturns400()
    {
        $this->expectException(\Pusher\ApiErrorException::class);
        $this->expectExceptionMessage('Batch too large');
        $this->expectExceptionCode('400');

        $batch = array();
        foreach (range(1, 11) as $i) {
            $batch[] = array('channel' => 'test_channel', 'name' => 'my_event', 'data' => array('index' => $i));
        }
        $this->pusher->triggerBatch($batch, true, false);
    }
}
