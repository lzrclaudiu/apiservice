<?php

namespace ApiService\Service;

use GuzzleHttp\Client;

class UserService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://reqres.in/api/']);
    }

    public function getUserById(int $id): UserDTO
    {
        $response = $this->client->get("users/{$id}");
        $data = json_decode($response->getBody()->getContents(), true)['data'];

        return new UserDTO($data['id'], $data['first_name'] . ' ' . $data['last_name']);
    }

    public function getUsers(int $page = 1): array
    {
        $response = $this->client->get("users?page={$page}");
        $users = json_decode($response->getBody()->getContents(), true)['data'];

        return array_map(function ($user) {
            return new UserDTO($user['id'], $user['first_name'] . ' ' . $user['last_name']);
        }, $users);
    }

    public function createUser(string $name, string $job): UserDTO
    {
        $response = $this->client->post("users", [
            'json' => ['name' => $name, 'job' => $job]
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        // Note: The API doesn't actually return an ID when creating a user, but let's assume it does for this example.
        return new UserDTO($data['id'], $name, $job, $data['createdAt']);
    }
}
