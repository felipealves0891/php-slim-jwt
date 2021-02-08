<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth;

use App\Application\Auth\AuthRepository;

class InMemoryAuthRepository implements AuthRepository
{
    /**
     * @var User[]
     */
    private $users;

    /**
     * @var string
     */
    private $filename;

    /**
     * InMemoryAuthRepository constructor.
     *
     * @param array|null $users
     */
    public function __construct(array $users = null)
    {
        // alves.211 - abc152
        // souza.3540 - xyz957
        $this->filename = "D:\apache24\htdocs\\var\cache\auth.json";
        $content = \file_get_contents($this->filename);
        $content = json_decode($content, true);
        $this->users = $users ?? $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(string $userId): array
    {
        if(!isset($this->users[$userId]))
            return [];

        return $this->users[$userId];
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(string $userId): array
    {
        $user = $this->getUser($userId);
        if(empty($user))
            return [];

        return isset($user['roles']) ? $user['roles']: [];
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(string $userId, string $hash, array $roles) : void
    {
        if(isset($this->users[$userId]))
            throw new \InvalidArgumentException("Usuario $userId já é cadastrado.");

        $this->users[$userId] = ['userId' => $userId, 'hash' => $hash, 'roles' => $roles];
        \file_put_contents($this->filename, json_encode($this->users));
    }

}
