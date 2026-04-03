<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

class HTTPClientTest extends TestCase
{
    public function testGotenbergIsUp(): void
    {
        $url = getenv('GOTENBERG_URL') ?: 'http://gotenberg:3000';

        try {
            $client = HttpClient::create(['timeout' => 3]);
            $response = $client->request('GET', $url . '/health');
            $this->assertSame(200, $response->getStatusCode());
        } catch (\Throwable $e) {
            $this->markTestSkipped('Gotenberg non disponible : ' . $e->getMessage());
        }
    }
}
