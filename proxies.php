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

namespace Drewlabs\Cors\Proxy;

use Drewlabs\Cors\Cors as CorsRequest;

/**
 * creates a cors request instance from provided configurations.
 *
 * @return CorsRequest
 */
function Cors(array $options = [])
{
    return new CorsRequest($options);
}
