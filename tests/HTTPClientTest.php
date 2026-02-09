<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

class HTTPClientTest extends TestCase
{
    public function testGotenbergIsUp(): void
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'http://gotenberg:3000/health');

        $this->assertSame(200, $response->getStatusCode());
    }
}
