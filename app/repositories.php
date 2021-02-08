<?php
declare(strict_types=1);

use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    // Here we map our UserRepository interface to its in memory implementation
    $containerBuilder->addDefinitions([
        \App\Domain\User\UserRepository::class => 
            \DI\autowire(\App\Infrastructure\Persistence\User\InMemoryUserRepository::class),
            
        \App\Application\Auth\AuthRepository::class => 
            \DI\autowire(\App\Infrastructure\Persistence\Auth\InMemoryAuthRepository::class),
    ]);
};
