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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface CorsInterface
{
    /**
     * returns whether or not the request is a CORS request.
     *
     * @return bool
     */
    public function isCorsRequest(RequestInterface $request);

    /**
     * returns whether or not the request is a preflight request.
     *
     * @return bool
     */
    public function isPreflightRequest(RequestInterface $request);

    /**
     * handles the actual request.
     *
     * @param ResponseInterface|mixed $response
     *
     * @return ResponseInterface|mixed
     */
    public function handleRequest(RequestInterface $request, $response);
}
