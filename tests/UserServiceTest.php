<?php

namespace ApiService\Tests;

use GuzzleHttp\Client;
use ApiService\DTO\UserDTO;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ApiService\Service\UserService;

class UserServiceTest extends TestCase
{
    public function testGetUserById()
    {
        $clientMock = $this->createMock(Client::class);
        $clientMock->method('get')->willReturn(new Response(
            200,
            [],
            json_encode(['data' => ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe']])
        ));

        // $userService = new UserService($clientMock);
        // $user = $userService->getUserById(1);

        print_r('test');

        // $this->assertInstanceOf(UserDTO::class, $user);
        // $this->assertEquals(1, $user->getId());
    }
}
