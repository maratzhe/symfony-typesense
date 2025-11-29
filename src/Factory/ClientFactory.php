<?php

declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Factory;

use Nyholm\Dsn\DsnParser;
use Typesense\Client;

class ClientFactory
{
    public static function create(string $dsn): Client
    {
        $dsn = self::parseDsn($dsn);

        return new Client([
            'nodes' => [
                [
                    'host' => $dsn['host'],
                    'port' => $dsn['port'],
                    'protocol' => $dsn['scheme'],
                ],
            ],
            'api_key' => $dsn['api_key'],
            'connection_timeout_seconds' => 5,
        ]);
    }

    /**
     * @return array{scheme: string, host: string, port: int, api_key: string}
     */
    public static function parseDsn(string $dsn) : array
    {
        $url    = parse_url($dsn);
        $query  = [];
        parse_str($url['query'] ?? '', $query);

        $key    = isset($query['api_key']) && is_string($query['api_key']) ? $query['api_key'] : '';
        return [
            'scheme'    => $url['scheme'] ?? '',
            'host'      => $url['host'] ?? 'localhost',
            'port'      => $url['port'] ?? 8108,
            'api_key'   => $key
        ];
    }
}
