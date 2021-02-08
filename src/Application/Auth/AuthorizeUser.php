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

class AuthorizeUser implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

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
        SettingsInterface $settings
    ) {
        $this->logger = $logger;
        $this->settings = $settings;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        if(!$request->hasHeader('Authorization'))
            throw new HttpUnauthorizedException($request);

        $token = $request->getHeader('Authorization');
        $token = \str_replace('Bearer ', '', $token[0]);

        $payload = $this->decode($token);
        $request->withAttribute('payload', $payload);
        return $handler->handle($request);
    }

    private function decode(string $token) : array 
    {
        try
        {
            $privateKey = $this->settings->get('private-key'); 
            $tokenEncoded = new TokenEncoded($token);
            $tokenEncoded->validate($privateKey, JWT::ALGORITHM_HS256);
            $payload = $tokenEncoded->decode()->getPayload();
            return $payload;
        } 
        catch(Nowakowskir\JWT\Exceptions\IntegrityViolationException $e)
        {
            throw $e;
        }
        catch(Nowakowskir\JWT\Exceptions\AlgorithmMismatchException $e)
        {
            throw $e;
        }
        catch(Nowakowskir\JWT\Exceptions\TokenExpiredException $e)
        {
            throw $e;
        }
        catch(Nowakowskir\JWT\Exceptions\TokenInactiveException $e)
        {
            throw $e;
        }
    }
}