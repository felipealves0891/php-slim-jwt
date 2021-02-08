<?php
declare(strict_types=1);

namespace App\Application\Auth;

use Psr\Log\LoggerInterface;
use Slim\Exception\HttpUnauthorizedException;
use App\Application\Settings\SettingsInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Nowakowskir\JWT\JWT;
use Nowakowskir\JWT\TokenDecoded;
use Nowakowskir\JWT\TokenEncoded;

class RegisterUser
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @var AuthRepository
     */
    protected $repository;

    /**
     * @var SettingsInterface
     */
    protected $settings;

    /**
     * @param LoggerInterface $logger
     * @param AuthRepository $repository 
     */
    public function __construct(
        LoggerInterface $logger,
        AuthRepository $repository,
        SettingsInterface $settings
    ) {
        $this->logger = $logger;
        $this->repository = $repository;
        $this->settings = $settings;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws HttpUnauthorizedException
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        $args = $request->getParsedBody();

        if(!isset($args['userName'], $args['password'], $args['roles']))
            throw new \InvalidArgumentException("Argumentos invalidos!");
        
        $userId = $args['userName'];
        $hash = $this->generateHash($args['password']);
        $roles = [];

        foreach ($args['roles'] as $key => $value) {
            if(\strtolower($value) == 'admin' || \strtolower($value) == 'guest')
                $roles[] = \strtolower($value);
        }

        $this->repository->setUser($userId, $hash, $roles);
        return $response->withStatus(201);
    }

    /**
     * @param string $password
     * @return string
     */
    private function generateHash(string $password) : string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

}

