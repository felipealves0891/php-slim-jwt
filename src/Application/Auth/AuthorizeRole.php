<?php
declare(strict_types=1);

namespace App\Application\Auth;

use Nowakowskir\JWT\JWT;
use Psr\Log\LoggerInterface;
use Nowakowskir\JWT\TokenEncoded;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpUnauthorizedException;
use App\Application\Settings\SettingsInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthorizeRole implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $role;

    /**
     * @param string $role 
     */
    public function __construct(string $role) 
    {
        $this->role = $role;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $attr = $request->getAttributes();
        if(!isset($attr['payload']))
            throw new HttpForbiddenException($request);

        $payload = $attr['payload'];
        if(!isset($payload['roles']) && is_array($payload['roles']))
            throw new HttpForbiddenException($request);
        
        foreach ($payload['roles'] as $key => $role)
            if($this->role == $role)
                return $handler->handle($request);

        throw new HttpForbiddenException($request);
    }
}