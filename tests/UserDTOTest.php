<?php

namespace ApiService\Tests;

use ApiService\DTO\UserDTO;
use PHPUnit\Framework\TestCase;

class UserDTOTest extends TestCase
{
    private $mockUserData = [
        2,
        'janet.weaver@reqres.in',
        'Janet',
        'Weaver',
        'https://reqres.in/img/faces/2-image.jpg'
    ];

    public function testClassConstructor()
    {
        $userDTO = new UserDTO(...$this->mockUserData);

        $this->assertSame(2, $userDTO->getId());
        $this->assertSame('janet.weaver@reqres.in', $userDTO->getEmail());
        $this->assertSame('Janet', $userDTO->getFirstName());
        $this->assertSame('Weaver', $userDTO->getLastName());
        $this->assertSame('https://reqres.in/img/faces/2-image.jpg', $userDTO->getAvatar());
    }

    public function testJsonSerialize()
    {
        $user = new UserDTO(...$this->mockUserData);

        $expectedArray = [
            'id' => 2,
            'email' => 'janet.weaver@reqres.in',
            'firstName' => 'Janet',
            'lastName' => 'Weaver',
            'avatar' => 'https://reqres.in/img/faces/2-image.jpg'
        ];

        $this->assertEquals($expectedArray, $user->jsonSerialize());
    }

    public function testJsonEncoding()
    {
        $user = new UserDTO(...$this->mockUserData);

        $expectedJson = '{"id":2,"email":"janet.weaver@reqres.in","firstName":"Janet","lastName":"Weaver","avatar":"https://reqres.in/img/faces/2-image.jpg"}';

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($user));
    }
}
