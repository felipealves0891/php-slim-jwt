<?php
declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'private-key' => file_get_contents(__DIR__ . '/private.key'), // command to generate - openssl genrsa -out private.key 2048
                'token-payload' => function(array $data = []) { // Conteudo do token
                    return [
                        'iat' => time(),
                        'jti' => md5((string)time()),
                        'iss' => 'my-app',
                        'nbf' => time(),
                        'exp' => strtotime('+10 minute'),
                        'data' => $data
                    ];
                }
            ]);
        }
    ]);
    
};
