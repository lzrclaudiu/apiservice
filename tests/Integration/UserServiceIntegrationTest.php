<?php

namespace ApiService\Tests;

use ApiService\DTO\UserDTO;
use PHPUnit\Framework\TestCase;
use ApiService\Service\UserService;
use ApiService\Exception\HttpUserNotFoundException;

class UserServiceIntegrationTest extends TestCase
{
    /**
     * @var UserService
     */
    private $userService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = new UserService();
    }

    // Tests for getUserById()
    public function testGetUserByIdReturnsUserDTOOnSuccess()
    {
        $user = $this->userService->getUserById(2);

        $this->assertInstanceOf(UserDTO::class, $user);

        $this->assertEquals(2, $user->getId());
    }

    public function testGetUserByIdThrowsUserNotFoundException()
    {
        $this->expectException(HttpUserNotFoundException::class);

        $this->userService->getUserById(9999);
    }

    // Tests for getUsers()
    public function testGetPaginatedUsersReturnsAValidArrayWithPaginationDataOnSuccess()
    {
        $data = $this->userService->getPaginatedUsers(2);

        $this->assertArrayHasKey('users', $data);
        $this->assertArrayHasKey('pagination', $data);

        $users = $data['users'];
        $pagination = $data['pagination'];

        $this->assertIsArray($users);
        $this->assertIsArray($pagination);

        $this->assertInstanceOf(UserDTO::class, $users[0]);
        $this->assertInstanceOf(UserDTO::class, end($users));

        $this->assertEquals(2, $pagination['page']);
    }

    public function testGetUsersEmptyResponseReturnsEmptyArray()
    {
        $data = $this->userService->getPaginatedUsers(9999);
        $users = $data['users'];

        $this->assertIsArray($users);
        $this->assertCount(0, $users);
    }

    // Tests for createUser()
    public function testCreateUserReturnsValidIDOnSuccess()
    {
        $userId = $this->userService->createUser('John Doe', 'Developer');

        $this->assertIsInt($userId);
    }
}
