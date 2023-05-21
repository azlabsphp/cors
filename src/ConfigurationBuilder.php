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

class ConfigurationBuilder
{
    /**
     * @var array<string,mixed>
     */
    private $array = [
        'allowed_hosts' => ['*'],
        'allowed_headers' => ['*'],
        'allowed_credentials' => true,
        'exposed_headers' => ['*'],
        'allowed_methods' => ['*'],
        'max_age' => 0
    ];


    /**
     * Creates new class instance
     * 
     * @return ConfigurationBuilder 
     */
    public static function new()
    {
        return new self();
    }

    /**
     * Add `allowed_hosts` to the configuration builder
     * 
     * @param mixed $hosts 
     * @return $this 
     */
    public function withHosts(...$hosts)
    {
        $this->array['allowed_hosts'] = array_unique($hosts);
        return $this;
    }

    /**
     * Add `allowed_headers` to the configuration builder
     * 
     * @param string[] ...$headers
     * 
     * @return self 
     */
    public function withHeaders(...$headers)
    {
        $this->array['allowed_headers'] = array_unique($headers);
        return $this;
    }

    /**
     * Add `allowed_methods` to the configuration builder
     * 
     * @param string[] ...$headers
     * 
     * @return self 
     */
    public function withMethods(...$methods)
    {
        $this->array['allowed_methods'] = array_unique($methods);
        return $this;
    }

    /**
     * Add `allowed_credentials` attribute to the configuration builder
     * 
     * @return self 
     */
    public function withCredentials()
    {
        $this->array['allowed_credentials'] = true;
        return $this;
    }

    /**
     * Add `exposed_headers` to the configuration builder
     * 
     * @param string[] ...$headers
     * 
     * @return self 
     */
    public function withExposedHeaders(...$headers)
    {
        $this->array['exposed_headers'] = array_unique($headers);
        return $this;
    }

    /**
     * Add `max_age` value to the cors configuration builder
     * 
     * @param int $age 
     * 
     * @return self 
     */
    public function withMaxAge(int $age)
    {
        $this->array['max_age'] = $age;
        return $this;
    }

    /**
     * Returns the array representation of the configuration
     * 
     * @return array<string, mixed> 
     */
    public function toArray()
    {
        return $this->array;
    }
}
