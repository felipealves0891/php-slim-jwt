<?php
declare(strict_types=1);

namespace App\Application\Auth;

interface AuthRepository
{
    /**
     * consult user data
     * 
     * @param string $userId
     * @return array
     */
    public function getUser(string $userId) : array; 

    /**
     * consult user data
     * 
     * @param string $userId
     * @return array
     */
    public function getRoles(string $userId) : array; 

    /**
     * set user data
     * 
     * @param string $userId
     * @param string $hash
     */
    public function setUser(string $userId, string $hash, array $roles) : void;
    
}