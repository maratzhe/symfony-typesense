<?php

declare(strict_types=1);

namespace Functional;

use Maratzhe\SymfonyTypesense\Factory\ClientFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class ClientFactoryTest extends KernelTestCase
{
    public function testManager() : void
    {
        $dsn    = 'http://localhost:8108?api_key=xyz';
        $data   = ClientFactory::parseDsn($dsn);

        static::assertSame('http', $data['scheme']);
        static::assertSame('localhost', $data['host']);
        static::assertSame(8108, $data['port']);
        static::assertSame('xyz', $data['api_key']);
    }
}