<?php

namespace ApiService\Service;

use GuzzleHttp\Client;
use ApiService\DTO\UserDTO;
use GuzzleHttp\Exception\GuzzleException;
use ApiService\Exception\HttpUserNotFoundException;
use ApiService\Exception\HttpRequestFailedException;
use ApiService\Exception\HttpInvalidResponseException;

class UserService
{
    public function __construct(private ?Client $client = null)
    {
        $this->client = $client ?? new Client(['base_uri' => 'https://reqres.in/api/']);
    }

    /**
     * @param int $id
     * 
     * @return UserDTO
     */
    public function getUserById(int $id): UserDTO
    {
        try {
            $response = $this->client->get("users/{$id}");

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['data'])) {
                throw new HttpInvalidResponseException("Data missing from the response.");
            }

            $data = $data['data'];
        } catch (GuzzleException $ex) {
            if ($ex->getCode() === 404) {
                throw new HttpUserNotFoundException("User not found for ID: {$id}.");
            }

            throw new HttpRequestFailedException("Failed to get user: " . $ex->getMessage());
        }

        return new UserDTO(
            $data['id'],
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['avatar'],
        );
    }

    /**
     * @param int $page
     * 
     * @return array
     */
    public function getPaginatedUsers(int $page = 1): array
    {
        try {
            $response = $this->client->get("users?page={$page}");

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data)) {
                return [
                    'users' => [],
                    'pagination' => [
                        'page' => 1,
                        'per_page' => 0,
                        'total' => 0,
                        'total_pages' => 1,
                    ],
                ];
            }

            if (!isset($data['data'])) {
                throw new HttpInvalidResponseException("Data missing from the response.");
            }

            if (!isset($data['page'])) {
                throw new HttpInvalidResponseException("Pagination data missing from the response.");
            }

            $users = array_map(function ($user) {
                return new UserDTO(
                    $user['id'],
                    $user['email'],
                    $user['first_name'],
                    $user['last_name'],
                    $user['avatar'],
                );
            }, $data['data']);

            $pagination = [
                'page' => $data['page'],
                'per_page' => $data['per_page'] ?? count($users),
                'total' => $data['total'] ?? null,
                'total_pages' => $data['total_pages'] ?? null,
            ];

            return [
                'users' => $users,
                'pagination' => $pagination
            ];
        } catch (GuzzleException $ex) {
            throw new HttpRequestFailedException("Failed to get paginated users: " . $ex->getMessage());
        }
    }

    /**
     * @param string $name
     * @param string $job
     * 
     * @return int
     */
    public function createUser(string $name, string $job): int
    {
        try {
            $response = $this->client->post("users", [
                'json' => ['name' => $name, 'job' => $job]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['id'])) {
                throw new HttpInvalidResponseException("Id missing from the response.");
            }

            return (int) $data['id'];
        } catch (GuzzleException $ex) {
            throw new HttpRequestFailedException("Error creating user: " . $ex->getMessage());
        }
    }
}
