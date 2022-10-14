<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use Pusher\Pusher;
use Pusher\PusherException;

class AuthenticateUserTest extends TestCase
{
    /**
     * @var Pusher
     */
    private $pusher;

    protected function setUp(): void
    {
        $this->pusher = new Pusher('thisisaauthkey', 'thisisasecret', 1, []);
    }

    public function testObjectConstruct(): void
    {
        $this->assertNotNull($this->pusher, 'Created new \Pusher\Pusher object');
    }

    public function testAuthenticateUser(): void
    {
        $auth_string = $this->pusher->authenticateUser('12345.6789', ['id' => '123']);
        self::assertEquals(
            '{"auth":"thisisaauthkey:fc713f433deb729d0d96f9e26ef054285cbc3e833ebe840b93722a2fa16a6a18","user_data":"{\"id\":\"123\"}"}',
            $auth_string,
            'Auth string valid'
        );
    }

    public function testAuthenticateUserUserData(): void
    {
        $auth_string = $this->pusher->authenticateUser('12345.6789', ['id' => '123', 'name' => 'John Smith']);
        self::assertEquals(
            '{"auth":"thisisaauthkey:0dddb208b53c7649f3fbbb86254a6e1986bc6f8b566423ea690c9ca773497373","user_data":"{\"id\":\"123\",\"name\":\"John Smith\"}"}',
            $auth_string,
            'Auth string valid'
        );
    }

    public function testInvalidSocketId(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->authorizeChannel('invalid-socket-id', '123');
    }

    public function testInvalidUserId(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->authenticateUser('12345.6789', ['id' => '']);
    }

    public function testInvalidInvalidUserData(): void
    {
        $this->expectException(PusherException::class);

        $this->pusher->authenticateUser('12345.6789', ['name' => 'John Smith']);
    }
}
