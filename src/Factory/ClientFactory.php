<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Factory;

use Nyholm\Dsn\DsnParser;
use Typesense\Client;

class ClientFactory
{
    public static function create(string $dsn): Client
    {
        $dsn = DsnParser::parse($dsn);

        return new Client([
            'nodes' => [
                [
                    'host' => $dsn->getHost(),
                    'port' => $dsn->getPort(),
                    'protocol' => $dsn->getScheme(),
                ],
            ],
            'api_key' => $dsn->getParameter('api_key'),
            'connection_timeout_seconds' => 5,
        ]);
    }
}
