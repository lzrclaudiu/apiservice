<?php

namespace ApiService\Tests;

use GuzzleHttp\Client;
use ApiService\DTO\UserDTO;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use ApiService\Service\UserService;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use PHPUnit\Framework\MockObject\MockObject;
use ApiService\Exception\HttpUserNotFoundException;
use ApiService\Exception\HttpRequestFailedException;
use ApiService\Exception\HttpInvalidResponseException;

class UserServiceTest extends TestCase
{
    /**
     * @var MockObject|Client
     */
    private $clientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(Client::class);
    }

    // Tests for getUserById()
    public function testGetUserByIdReturnsUserDTOOnSuccess()
    {
        $this->clientMock->method('get')->willReturn(new Response(
            200,
            [],
            json_encode([
                'data' => [
                    'id' => 2,
                    'email' => 'janet.weaver@reqres.in',
                    'first_name' => 'Janet',
                    'last_name' => 'Weaver',
                    'avatar' => 'https://reqres.in/img/faces/2-image.jpg'
                ]
            ])
        ));

        $userService = new UserService($this->clientMock);
        $user = $userService->getUserById(2);

        $this->assertInstanceOf(UserDTO::class, $user);

        $this->assertEquals(2, $user->getId());
        $this->assertEquals('janet.weaver@reqres.in', $user->getEmail());
        $this->assertEquals('Janet', $user->getFirstName());
        $this->assertEquals('Weaver', $user->getLastName());
        $this->assertEquals('https://reqres.in/img/faces/2-image.jpg', $user->getAvatar());
    }

    public function testGetUserByIdThrowsUserNotFoundException()
    {
        $this->clientMock->method('get')->willThrowException(
            new ClientException('Testing exception', new Request('GET', 'test'), new Response(404))
        );

        $userService = new UserService($this->clientMock);

        $this->expectException(HttpUserNotFoundException::class);

        $userService->getUserById(9999);
    }

    public function testGetUserByIdThrowsRequestFailedException()
    {
        $this->clientMock->method('get')->willThrowException(
            new ServerException('Testing exception', new Request('GET', 'test'), new Response(503))
        );

        $userService = new UserService($this->clientMock);

        $this->expectException(HttpRequestFailedException::class);

        $userService->getUserById(2);
    }

    public function testGetUserByIdThrowsInvalidResponseExceptionOnUnexpectedValue()
    {
        $this->clientMock->method('get')->willReturn(new Response(
            200,
            [],
            json_encode([
                'invalid_data' => [
                    'id' => 'test',
                ]
            ])
        ));

        $userService = new UserService($this->clientMock);

        $this->expectException(HttpInvalidResponseException::class);

        $userService->getUserById(2);
    }

    // Tests for getPaginatedUsers()
    public function testGetPaginatedUsersReturnsAValidArrayWithPaginationDataOnSuccess()
    {
        $responseBody = json_encode([
            'data' => [
                [
                    'id' => 1,
                    'email' => "george.bluth@reqres.in",
                    'first_name' => "George",
                    'last_name' => "Bluth",
                    'avatar' => "https://reqres.in/img/faces/1-image.jpg"
                ],
                [
                    'id' => 2,
                    'email' => 'janet.weaver@reqres.in',
                    'first_name' => 'Janet',
                    'last_name' => 'Weaver',
                    'avatar' => 'https://reqres.in/img/faces/2-image.jpg'
                ]
            ],
            'page' => 1,
            'per_page' => 2,
            'total' => 2,
            'total_pages' => 1

        ]);

        $this->clientMock->method('get')->willReturn(new Response(200, [], $responseBody));

        $userService = new UserService($this->clientMock);
        $data = $userService->getPaginatedUsers();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('users', $data);
        $this->assertIsArray($data['users']);
        $this->assertArrayHasKey('pagination', $data);
        $this->assertIsArray($data['pagination']);

        $users = $data['users'];
        $pagination = $data['pagination'];

        $this->assertCount(2, $users);
        $this->assertInstanceOf(UserDTO::class, $users[0]);
        $this->assertEquals(1, $users[0]->getId());
        $this->assertEquals("george.bluth@reqres.in", $users[0]->getEmail());
        $this->assertEquals("George", $users[0]->getFirstName());
        $this->assertEquals("Bluth", $users[0]->getLastName());
        $this->assertEquals("https://reqres.in/img/faces/1-image.jpg", $users[0]->getAvatar());

        $this->assertEquals(2, $users[1]->getId());
        $this->assertEquals("janet.weaver@reqres.in", $users[1]->getEmail());
        $this->assertEquals("Janet", $users[1]->getFirstName());
        $this->assertEquals("Weaver", $users[1]->getLastName());
        $this->assertEquals("https://reqres.in/img/faces/2-image.jpg", $users[1]->getAvatar());

        $this->assertEquals(1, $pagination['page']);
        $this->assertEquals(2, $pagination['per_page']);
        $this->assertEquals(2, $pagination['total']);
        $this->assertEquals(1, $pagination['total_pages']);
    }

    public function testGetUsersThrowsRequestFailedException()
    {
        $this->clientMock->method('get')->willThrowException(
            new ServerException('Testing exception', new Request('GET', 'test'), new Response(503))
        );

        $userService = new UserService($this->clientMock);

        $this->expectException(HttpRequestFailedException::class);

        $userService->getPaginatedUsers();
    }

    public function testGetUsersThrowsInvalidResponseExceptionOnUnexpectedValue()
    {
        $this->clientMock->method('get')->willReturn(new Response(
            200,
            [],
            json_encode([
                'invalid_data' => [
                    'id' => 'test',
                ]
            ])
        ));

        $userService = new UserService($this->clientMock);

        $this->expectException(HttpInvalidResponseException::class);

        $userService->getPaginatedUsers();
    }

    public function testGetUsersEmptyResponseReturnsEmptyArray()
    {
        $this->clientMock->method('get')->willReturn(new Response(
            200,
            [],
            json_encode([])
        ));

        $userService = new UserService($this->clientMock);

        $data = $userService->getPaginatedUsers();
        $users = $data['users'];

        $this->assertIsArray($users);
        $this->assertCount(0, $users);
    }

    // Tests for createUser()
    public function testCreateUserOnSuccess()
    {
        $this->clientMock->method('post')->willReturn(new Response(
            201,
            [],
            json_encode([
                'id' => '12',
                'name' => 'John Doe',
                'job' => 'Web Developer',
                'createdAt' => '2024-03-26T12:54:54.602Z'
            ])
        ));

        $userService = new UserService($this->clientMock);
        $userId = $userService->createUser('John Doe', 'Developer');

        $this->assertIsInt($userId);
        $this->assertEquals('12', $userId);
    }

    public function testCreateUserThrowsRequestFailedException()
    {
        $this->clientMock->method('post')->willThrowException(
            new ClientException('Testing exception', new Request('POST', 'test'), new Response(503))
        );

        $userService = new UserService($this->clientMock);

        $this->expectException(HttpRequestFailedException::class);

        $userService->createUser('John Doe', 'Web Developer');
    }

    public function testCreateUserThrowsInvalidResponseExceptionOnUnexpectedValue()
    {
        $this->clientMock->method('post')->willReturn(new Response(
            200,
            [],
            json_encode([
                'invalid_data' => [
                    'id' => 'test',
                ]
            ])
        ));

        $userService = new UserService($this->clientMock);

        $this->expectException(HttpInvalidResponseException::class);

        $userService->createUser('John Doe', 'Web Developer');
    }
}
