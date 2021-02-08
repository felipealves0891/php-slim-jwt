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

class AuthenticateUser
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

        if(!isset($args['userName'], $args['password']))
            throw new HttpUnauthorizedException($request);

        $user = $this->repository->getUser($args['userName']);
        if(empty($user))
            throw new HttpUnauthorizedException($request);

        if(!password_verify($args['password'], $user['hash']))
            throw new HttpUnauthorizedException($request);
        
        $roles = $this->repository->getRoles($args['userName']);
        $token =  $this->generateToken([
            'userId' => $args['userName'],
            'roles' => $roles
        ]);

        return $response
                    ->withStatus(200)
                    ->withHeader("Authorization", "Bearer $token");
    }

    /**
     * @param array $user
     * @return string
     */
    private function generateToken(array $user) : string
    {
        $privateKey = $this->settings->get('private-key'); 
        $payload = $this->settings->get('token-payload')($user);
        $header = [
            'alg' => 'HS256',
            "typ" => "JWT"
        ];

        $tokenDecoded = new TokenDecoded($payload, $header);
        $tokenEncoded = $tokenDecoded->encode($privateKey, JWT::ALGORITHM_HS256);
        return $tokenEncoded->toString();
    }

}

