<?php

namespace acceptance;

use Error;
use PHPUnit\Framework\TestCase;
use Pusher\Pusher;
use stdClass;

class TriggerBatchAsyncTest extends TestCase
{
    /**
     * @var Pusher
     */
    private $pusher;

    protected function setUp(): void
    {
        if (PUSHERAPP_AUTHKEY === '' || PUSHERAPP_SECRET === '' || PUSHERAPP_APPID === '') {
            $this->markTestSkipped('Please set the
            PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET and
            PUSHERAPP_APPID keys.');
        } else {
            $this->pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, ['cluster' => PUSHERAPP_CLUSTER]);
        }
    }

    public function testObjectConstruct(): void
    {
        self::assertNotNull($this->pusher, 'Created new \Pusher\Pusher object');
    }

    public function testSimplePush(): void
    {
        $batch = [];
        $batch[] = ['channel' => 'test_channel', 'name' => 'my_event', 'data' => ['my' => 'data']];
        $result = $this->pusher->triggerBatchAsync($batch)->wait();
        $this->assertEquals(new stdClass(), $result);
    }

    public function testTLSPush(): void
    {
        $options = [
            'useTLS' => true,
            'cluster' => PUSHERAPP_CLUSTER,
        ];
        $pc = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $batch = [];
        $batch[] = ['channel' => 'test_channel', 'name' => 'my_event', 'data' => ['my' => 'data']];
        $result = $pc->triggerBatchAsync($batch)->wait();
        self::assertEquals(new stdClass(), $result);
    }

    public function testTriggerBatchNonEncryptedEventsWithObjectPayloads(): void
    {
        $options = [
            'useTLS' => true,
            'cluster' => PUSHERAPP_CLUSTER,
        ];
        $pc = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $batch = [];
        $batch[] = ['channel' => 'test_channel', 'name' => 'my_event', 'data' => ['my' => 'data']];
        $batch[] = ['channel' => 'mio_canale', 'name' => 'my_event2', 'data' => ['my' => 'data2']];
        $result = $pc->triggerBatchAsync($batch)->wait();
        self::assertEquals(new stdClass(), $result);
    }

    public function testTriggerBatchWithSingleEvent(): void
    {
        $options = [
            'useTLS' => true,
            'cluster' => PUSHERAPP_CLUSTER,
        ];
        $pc = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $batch = [];
        $batch[] = ['channel' => 'test_channel', 'name' => 'my_event', 'data' => 'test-string'];
        $result = $pc->triggerBatchAsync($batch)->wait();
        self::assertEquals(new stdClass(), $result);
    }

    public function testTriggerBatchWithInfo(): void
    {
        $options = [
            'useTLS' => true,
            'cluster' => PUSHERAPP_CLUSTER,
        ];
        $pc = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $expectedMyChannel = new stdClass();
        $expectedMyChannel->subscription_count = 1;
        $expectedMyChannel2 = new stdClass();
        $expectedPresenceMyChannel = new stdClass();
        $expectedPresenceMyChannel->user_count = 0;
        $expectedPresenceMyChannel->subscription_count = 0;
        $expectedResult = new stdClass();
        $expectedResult->batch = [
            $expectedMyChannel,
            $expectedMyChannel2,
            $expectedPresenceMyChannel
        ];

        $batch = [];
        $batch[] = ['channel' => TEST_CHANNEL, 'name' => 'my_event', 'data' => 'test-string', 'info' => 'subscription_count'];
        $batch[] = ['channel' => 'my-channel-2', 'name' => 'my_event', 'data' => 'test-string'];
        $batch[] = ['channel' => 'presence-my-channel', 'name' => 'my_event', 'data' => 'test-string', 'info' => 'user_count,subscription_count'];
        $result = $pc->triggerBatchAsync($batch)->wait();
        self::assertEquals($expectedResult, $result);
    }

    public function testTriggerBatchWithMultipleNonEncryptedEventsWithStringPayloads(): void
    {
        $options = [
            'useTLS' => true,
            'cluster' => PUSHERAPP_CLUSTER,
        ];
        $pc = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $batch = [];
        $batch[] = ['channel' => 'test_channel', 'name' => 'my_event', 'data' => 'test-string'];
        $batch[] = ['channel' => 'test_channel2', 'name' => 'my_event2', 'data' => 'test-string2'];
        $result = $pc->triggerBatchAsync($batch)->wait();
        self::assertEquals(new stdClass(), $result);
    }

    public function testTriggerBatchWithMultipleCombinationsofStringAndObjectPayloads(): void
    {
        $options = [
            'useTLS' => true,
            'cluster' => PUSHERAPP_CLUSTER,
        ];
        $pc = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $batch = [];
        $batch[] = ['channel' => 'test_channel', 'name' => 'my_event', 'data' => 'test-string'];
        $batch[] = ['channel' => 'test_channel2', 'name' => 'my_event2', 'data' => ['my' => 'data2']];
        $result = $pc->triggerBatchAsync($batch)->wait();
        self::assertEquals(new stdClass(), $result);
    }

    public function testTriggerBatchWithWithEncryptedEventSuccess(): void
    {
        $options = [
            'useTLS'  => true,
            'cluster' => PUSHERAPP_CLUSTER,
            'encryption_master_key_base64' => 'Y0F6UkgzVzlGWk0zaVhxU05JR3RLenR3TnVDejl4TVY=',
        ];
        $pc = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $batch = [];
        $batch[] = ['channel' => 'private-encrypted-test_channel', 'name' => 'my_event', 'data' => 'test-string'];
        $result = $pc->triggerBatchAsync($batch)->wait();
        self::assertEquals(new stdClass(), $result);
    }

    public function testTriggerBatchWithMultipleEncryptedEventsSuccess(): void
    {
        $options = [
            'useTLS' => true,
            'cluster' => PUSHERAPP_CLUSTER,
            'encryption_master_key_base64' => 'Y0F6UkgzVzlGWk0zaVhxU05JR3RLenR3TnVDejl4TVY=',
        ];
        $pc = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $batch = [];
        $batch[] = ['channel' => 'test_channel', 'name' => 'my_event', 'data' => 'test-string'];
        $batch[] = ['channel' => 'private-encrypted-test_channel2', 'name' => 'my_event2', 'data' => 'test-string2'];
        $result = $pc->triggerBatchAsync($batch)->wait();
        self::assertEquals(new stdClass(), $result);
    }

    public function testTriggerBatchWithMultipleCombinationsofStringsAndObjectsWithEncryptedEventSuccess(): void
    {
        $options = [
            'useTLS' => true,
            'cluster' => PUSHERAPP_CLUSTER,
            'encryption_master_key_base64' => 'Y0F6UkgzVzlGWk0zaVhxU05JR3RLenR3TnVDejl4TVY=',
        ];
        $pc = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $batch = [];
        $batch[] = ['channel' => 'test_channel', 'name' => 'my_event', 'data' => 'secret-string'];
        $batch[] = ['channel' => 'private-encrypted-test_channel2', 'name' => 'my_event2', 'data' => ['my' => 'data2']];
        $result = $pc->triggerBatchAsync($batch)->wait();
        self::assertEquals(new stdClass(), $result);
    }

    public function testTriggerBatchMultipleEventsWithEncryptedEventWithoutEncryptionMasterKeyError(): void
    {
        $this->expectException(Error::class);

        $options = [
            'useTLS' => true,
            'cluster' => PUSHERAPP_CLUSTER,
        ];
        $pc = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $batch = [];
        $batch[] = ['channel' => 'my_test_chan', 'name' => 'my_event', 'data' => ['my' => 'data']];
        $batch[] = ['channel' => 'private-encrypted-ceppaio', 'name' => 'my_private_encrypted_event', 'data' => ['my' => 'to_be_encrypted_data_shhhht']];
        $pc->triggerBatchAsync($batch)->wait();
    }

    public function testTriggerBatchWithMultipleEncryptedEventsWithEncryptionMasterKeySuccess(): void
    {
        $options = [
            'useTLS'                       => true,
            'cluster' => PUSHERAPP_CLUSTER,
            'encryption_master_key_base64' => 'Y0F6UkgzVzlGWk0zaVhxU05JR3RLenR3TnVDejl4TVY=',
        ];
        $pc = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $batch = [];
        $batch[] = ['channel' => 'my_test_chan', 'name' => 'my_event', 'data' => ['my' => 'data']];
        $batch[] = ['channel' => 'private-encrypted-ceppaio', 'name' => 'my_private_encrypted_event', 'data' => ['my' => 'to_be_encrypted_data_shhhht']];
        $result = $pc->triggerBatchAsync($batch)->wait();
        self::assertEquals(new stdClass(), $result);
    }

    public function testSendingOver10kBMessageReturns413(): void
    {
        $this->expectException(\Pusher\ApiErrorException::class);
        $this->expectExceptionMessage('content of this event');
        $this->expectExceptionCode('413');

        $data = str_pad('', 11 * 1024, 'a');
        $batch = [];
        $batch[] = ['channel' => 'test_channel', 'name' => 'my_event', 'data' => $data];
        $this->pusher->triggerBatchAsync($batch, true)->wait();
    }

    public function testSendingOver10messagesReturns400(): void
    {
        $this->expectException(\Pusher\ApiErrorException::class);
        $this->expectExceptionMessage('Batch too large');
        $this->expectExceptionCode('400');

        $batch = [];
        foreach (range(1, 11) as $i) {
            $batch[] = ['channel' => 'test_channel', 'name' => 'my_event', 'data' => ['index' => $i]];
        }
        $this->pusher->triggerBatchAsync($batch, false)->wait();
    }

    public function testTriggeringApiExceptionIfConnectionErrorOcurred(): void
    {
        $this->expectException(\Pusher\ApiErrorException::class);

        $options = ['host' => 'invalidhost'];
        $this->pusher = new Pusher(PUSHERAPP_AUTHKEY, PUSHERAPP_SECRET, PUSHERAPP_APPID, $options);

        $batch = [['channel' => 'test_channel', 'name' => 'my_event', 'data' => ['index' => 0]]];
        $this->pusher->triggerBatchAsync($batch, false)->wait();
    }
}
