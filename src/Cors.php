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

namespace Drewlabs\Cors;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Cors implements CorsInterface
{
    // #region Class constants
    /**
     * @var string[]
     */
    const ALLOWED_DEFAULT_HEADERS = [
        'X-Requested-With',
        'Content-Type',
        'Accept',
        'Origin',
        'Authorization',
        'Application',
        'Cache-Control'
    ];

    /**
     * @var string[]
     */
    const ALLOWED_DEFAULT_METHODS = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];
    // #endregion Class constants


    /**
     * Access control max age header value.
     *
     * @var int
     */
    private $max_age = 0;

    /**
     * @var bool
     */
    private $allowed_credentials = true;

    /**
     * @var array<string>
     */
    private $allowed_hosts = [];

    /**
     * @var string[]
     */
    private $allowed_methods = [];

    /**
     * @var string[]
     */
    private $allowed_headers = [];

    /**
     * @var array<string,mixed>
     */
    private $exposed_headers = [];

    /**
     * List of dynamic properties of the current object.
     *
     * @var string[]
     */
    private $properties = [
        'allowed_hosts',
        'allowed_methods',
        'max_age',
        'allowed_headers',
        'allowed_credentials',
        'exposed_headers',
    ];

    /**
     * Creates class instance.
     *
     * @return void
     */
    public function __construct(array $config = null)
    {
        $this->forceFill($config ?? []);
    }

    public function isCorsRequest(RequestInterface $request)
    {
        return $this->hasHeader($request, 'Origin');
    }

    public function isPreflightRequest(RequestInterface $request)
    {
        return $this->isCorsRequest($request) &&
            $this->isMethod($request, 'OPTIONS') &&
            $this->hasHeader($request, 'Access-Control-Request-Method');
    }

    public function handleRequest(RequestInterface $request, $response)
    {
        if ($this->isPreflightRequest($request)) {
            return $this->handlePreflightRequest($request, $response);
        }
        // Do not set any headers if the origin is not allowed
        if ($this->matches($this->allowed_hosts, $this->getHeader($request, 'Origin'))) {
            return $this->handleNormalRequest($request, $response);
        }

        return $response;
    }

    public function handlePreflightRequest($request, $response)
    {
        // Do not set any headers if the origin is not allowed
        if ($this->matches($this->allowed_hosts, $this->getHeader($request, 'Origin'))) {
            // Set the allowed origin if it is a preflight request
            $response = $this->setAllowOriginHeaders($request, $response);
            // Set headers max age
            if ($this->max_age) {
                $response = $this->setHeader($response, 'Access-Control-Max-Age', (string) $this->max_age);
            }
            // Set the allowed method headers
            $response = $this->setHeaders(
                $response,
                [
                    'Access-Control-Allow-Credentials' => $this->allowed_credentials ? 'true' : 'false',
                    'Access-Control-Allow-Methods' => \in_array('*', $this->allowed_methods, true)
                        ? strtoupper($this->getHeader($request, 'Access-Control-Request-Method', implode(",", self::ALLOWED_DEFAULT_METHODS)))
                        : implode(',', $this->allowed_methods),
                    'Access-Control-Allow-Headers' => \in_array('*', $this->allowed_headers, true)
                        ? strtolower($this->getHeader($request, 'Access-Control-Request-Headers', implode(",", self::ALLOWED_DEFAULT_HEADERS)))
                        : implode(',', $this->allowed_headers),
                ]
            );
        }

        return $response;
    }

    public function handleNormalRequest($request, $response)
    {
        $response = $this->setAllowOriginHeaders($request, $response);
        // Set Vary unless all origins are allowed
        if (!\in_array('*', $this->allowed_hosts, true)) {
            $vary = $this->hasHeader($request, 'Vary') ? $this->getHeader($request, 'Vary') . ', Origin' : 'Origin';
            $response = $this->setHeader($response, 'Vary', $vary);
        }
        $response = $this->setHeader($response, 'Access-Control-Allow-Credentials', $this->allowed_credentials ? 'true' : 'false');

        if (!empty($this->exposed_headers)) {
            $response = $this->setHeader($response, 'Access-Control-Expose-Headers', implode(',', $this->exposed_headers));
        }

        return $response;
    }

    private function forceFill(array $config)
    {
        foreach ($this->properties as $key) {
            if (\array_key_exists($key, $config) && null !== ($config[$key] ?? null)) {
                $prop = $this->{$key};
                $current = is_string($item = $config[$key]) && is_array($prop) ? array_unique(array_merge($prop, [$item])) : $item;
                // **Note*
                // By default if the allowed_hosts entry is empty we use ['*'] to allow
                // request from any origin
                if ('allowed_hosts' === $key) {
                    $current = \is_string($current) ? [$current] : (empty($current) ? ['*'] : $current);
                }
                $this->{$key} = $current;
            }
        }
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return MessageInterface
     */
    private function setAllowOriginHeaders($request, $response)
    {
        $origin = $this->getHeader($request, 'Origin');
        if (\in_array('*', $this->allowed_hosts, true)) {
            $response = $this->setHeader($response, 'Access-Control-Allow-Origin', empty($origin) ? '*' : $origin);
        } elseif ($this->matches($this->allowed_hosts, $origin)) {
            $response = $this->setHeader($response, 'Access-Control-Allow-Origin', $origin);
        }

        return $response;
    }

    /**
     * Create a pattern for a wildcard, based on $this->matches() from Laravel.
     *
     * @param string $pattern
     *
     * @return string
     */
    private function convertWildcardToPattern($pattern)
    {
        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern);

        return '#^' . $pattern . '\z#u';
    }

    private function matches($pattern, $value)
    {
        $patterns = \is_array($pattern) ? $pattern : [$pattern];

        $value = (string) $value;

        if (empty($patterns)) {
            return false;
        }
        foreach ($patterns as $pattern) {
            $pattern = (string) $pattern;
            if ($pattern === $value) {
                return true;
            }
            if (1 === preg_match($this->convertWildcardToPattern($pattern), $value)) {
                return true;
            }
        }

        return false;
    }

    // #region Miscellanous mehods
    /**
     * Return the HTTP Message header value or $default if the header is not present.
     *
     * @param mixed $default
     *
     * @return string
     */
    private function getHeader(RequestInterface $request, string $name, $default = null)
    {
        $headers = $request->getHeader($name);

        return array_pop($headers) ?? $default;
    }

    /**
     * Checks if the request has a given header.
     *
     * @return bool
     */
    private function hasHeader(RequestInterface $request, string $header)
    {
        return null !== $this->getHeader($request, $header, null);
    }

    /**
     * Checks if the request method equals a given method.
     *
     * @return bool
     */
    private function isMethod(RequestInterface $request, string $method)
    {
        return strtoupper($request->getMethod()) === strtoupper($method);
    }

    /**
     * Set HTTP message header value.
     *
     * @param mixed $value
     *
     * @throws \InvalidArgumentException
     *
     * @return MessageInterface
     */
    private function setHeader(MessageInterface $message, string $header, $value)
    {
        return $message->withHeader($header, $value);
    }

    /**
     * Set a list of header into the http message.
     *
     * @throws \InvalidArgumentException
     *
     * @return MessageInterface
     */
    private function setHeaders(MessageInterface $message, array $headers)
    {
        foreach ($headers as $name => $value) {
            $message = $message->withHeader($name, $value);
        }

        return $message;
    }
    // #endregion Miscellanous mehods
}
