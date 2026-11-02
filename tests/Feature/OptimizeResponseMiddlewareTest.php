<?php

namespace Tests\Feature;

use App\Http\Middleware\OptimizeResponseMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class OptimizeResponseMiddlewareTest extends TestCase
{
    private function respond(array $headers = []): \Symfony\Component\HttpFoundation\Response
    {
        $request = Request::create('/anything', 'GET', server: collect($headers)
            ->mapWithKeys(fn ($v, $k) => ['HTTP_'.strtoupper(str_replace('-', '_', $k)) => $v])
            ->all());

        return (new OptimizeResponseMiddleware)->handle(
            $request,
            fn () => new Response(str_repeat('hello world ', 500), 200, ['Content-Type' => 'text/html'])
        );
    }

    public function test_gzips_large_responses_and_varies_on_accept_encoding(): void
    {
        $response = $this->respond(['Accept-Encoding' => 'gzip']);

        $this->assertSame('gzip', $response->headers->get('Content-Encoding'));
        $this->assertStringContainsString('Accept-Encoding', (string) $response->headers->get('Vary'));
        $this->assertSame(str_repeat('hello world ', 500), gzdecode($response->getContent()));
    }

    public function test_skips_gzip_when_disabled(): void
    {
        config(['performance.gzip' => false]);

        $response = $this->respond(['Accept-Encoding' => 'gzip']);

        $this->assertNull($response->headers->get('Content-Encoding'));
        $this->assertSame(str_repeat('hello world ', 500), $response->getContent());
    }

    public function test_returns_304_when_etag_matches(): void
    {
        $etag = $this->respond()->headers->get('ETag');
        $this->assertNotEmpty($etag);

        $this->assertSame(304, $this->respond(['If-None-Match' => $etag])->getStatusCode());
    }
}
