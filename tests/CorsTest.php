<?php

declare(strict_types=1);

/*
 * This file is part of the Drewlabs package.
 *
 * (c) Sidoine Azandrew <azandrewdevelopper@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Drewlabs\Cors\ConfigurationBuilder;
use Drewlabs\Cors\Cors;
use Drewlabs\Cors\CorsInterface;
use function Drewlabs\Cors\Proxy\Cors;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

use PHPUnit\Framework\TestCase;

class CorsTest extends TestCase
{
    public function test_constructor()
    {
        $service = new Cors([
            'allowed_hosts' => ['*'],
            'allowed_headers' => [],
            'allowed_credentials' => false,
            'exposed_headers' => [],
            'max_age' => 0,
        ]);

        $this->assertInstanceOf(CorsInterface::class, $service);
    }

    public function test_cors_proxy_function()
    {
        $cors = Cors();
        $this->assertInstanceOf(CorsInterface::class, $cors);
    }

    public function test_is_prelift_request()
    {
        $service = Cors([
            'allowed_hosts' => ['*'],
            'allowed_headers' => [],
            'allowed_credentials' => false,
            'exposed_headers' => [],
            'max_age' => 0,
        ]);
        $request = $this->createPsr7Request();
        $request = $request->withMethod('OPTIONS');
        $request = $request->withHeader('Origin', 'http://localhost');
        $request = $request->withHeader('Access-Control-Request-Method', 'GET');
        $this->assertTrue($service->isPreflightRequest($request));
    }

    public function test_is_cors_request()
    {
        $service = new Cors([
            'allowed_hosts' => ['*'],
            'allowed_headers' => [],
            'allowed_credentials' => false,
            'exposed_headers' => [],
            'max_age' => 0,
        ]);
        $request = $this->createPsr7Request();
        $request = $request->withHeader('Origin', 'http://localhost');
        $this->assertTrue($service->isCorsRequest($request));
    }

    public function test_is_cors_request_return_false()
    {
        $service = new Cors([
            'allowed_hosts' => ['*'],
            'allowed_headers' => [],
            'allowed_credentials' => false,
            'exposed_headers' => [],
            'max_age' => 0,
        ]);
        $request = $this->createPsr7Request();
        $this->assertFalse($service->isCorsRequest($request));
    }

    public function test_handle_preflight_request()
    {
        $service = new Cors([
            'allowed_hosts' => [],
            'allowed_headers' => [],
            'allowed_credentials' => false,
            'exposed_headers' => [],
            'max_age' => 0,
        ]);
        $request = $this->createPsr7Request();
        $request = $request->withHeader('Origin', 'http://localhost');
        /**
         * @var \Nyholm\Psr7\Response
         */
        $response = $service->handlePreflightRequest($request, new \Nyholm\Psr7\Response());
        $headers = $response->getHeader('Access-Control-Allow-Origin');
        $this->assertSame('http://localhost', array_pop($headers));
    }

    public function test_handle_preflight_request_for_origin()
    {
        $service = new Cors([
            'allowed_hosts' => [
                'http://localhost',
            ],
            'allowed_headers' => [],
            'allowed_credentials' => false,
            'exposed_headers' => [],
            'max_age' => 0,
        ]);
        $request = $this->createPsr7Request();
        $request = $request->withHeader('Origin', 'http://localhost');
        /**
         * @var \Nyholm\Psr7\Response
         */
        $response = $service->handlePreflightRequest($request, new \Nyholm\Psr7\Response());
        $headers = $response->getHeader('Access-Control-Allow-Origin');
        $this->assertSame('http://localhost', array_pop($headers));
    }

    public function test_normal_request()
    {
        $service = new Cors([
            'allowed_hosts' => [
                '*',
            ],
            'allowed_headers' => [],
            'allowed_credentials' => false,
            'exposed_headers' => [],
            'max_age' => 0,
        ]);
        $request = $this->createPsr7Request();
        // $request = $request->withHeader('Origin', 'http://localhost');
        /**
         * @var \Nyholm\Psr7\Response
         */
        $response = $service->handleNormalRequest($request, new \Nyholm\Psr7\Response());
        $headers = $response->getHeader('Access-Control-Allow-Origin');
        $this->assertSame('*', array_pop($headers));
    }

    public function test_handle_request_using_config_builder()
    {
        $service = new Cors(
            ConfigurationBuilder::new()
                ->withHosts('http://localhost')
                ->withCredentials()
                ->withMaxAge(0)
                ->withMethods('POST')
                ->toArray()
        );
        $request = $this->createPsr7Request();
        $request = $request->withHeader('Origin', 'http://localhost');
        /**
         * @var \Nyholm\Psr7\Response
         */
        $response = $service->handlePreflightRequest($request, new \Nyholm\Psr7\Response());
        $headers = $response->getHeader('Access-Control-Allow-Origin');
        $this->assertSame('http://localhost', array_pop($headers));
        $result = $response->getHeader('Access-Control-Allow-Methods');
        $this->assertEquals(implode(",", ['POST']), array_pop($result));
    }

    private function createPsr7Request()
    {
        $psr17Factory = new Psr17Factory();
        $psrHttpFactory = new ServerRequestCreator(
            $psr17Factory, // ServerRequestFactory
            $psr17Factory, // UriFactory
            $psr17Factory, // UploadedFileFactory
            $psr17Factory  // StreamFactory
        );

        return $psrHttpFactory->fromGlobals();
    }
}
