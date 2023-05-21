# Cors

This library provides HTTP cors handlers for PHP projects based on `Psr7` requests.

## Usage

### Default instance

The library comes with a default instance that can be created through it constructor method:

```php
use Drewlabs\Cors\Cors;

$cors = new Cors();
```

**Note** The example above uses the default constructor which creates the instance using default configuration values which does not allow any `header`, nor `host`. To customize the default configuration, the constructor
accepts a configuration dictionnary (PHP array) as parameter.

- Accepting a specific list of origin or hosts

The example below configures the cors instance to accept request only from `http://localhost:3000` as origin.

```php
use Drewlabs\Cors\Cors;

$cors = new Cors([ 'allowed_hosts' => 'http://localhost:3000', 'allowed_headers' => ['*'], 'allowed_methods' => ['*'] ]);
```

**Note** The example above configure the cors to allow request from `http://localhost:3000`, using `any` headers and `any` methods.

#### Using configuration builder

To avoid typo errors, the library provide a fluent builder instance for building configuration values:

```php
use Drewlabs\Cors\Cors;
use Drewlabs\Cors\ConfigurationBuilder;

$cors = new Cors(
    ConfigurationBuilder::new()
        // Add `Origin: http://localhost` header
        ->withHosts('http://localhost', 'http://localhost:3000')
        // Add an `allowed_credentials: yes` header
        ->withCredentials()
        ->withMaxAge(0)
        // Add an `Access-Control-Allow-Methods: POST` header configuration value
        ->withMethods('POST')
        // Convert the builded configuration to array
        ->toArray()
);
```

### Handling Psr request

The library provides a `handleRequest` method for handling `PSR7` compatible requests.

```php
use Drewlabs\Cors\Cors;

$cors = new Cors();

$cors->handleRequest(new Request());
```

### Checking if request is a cors request

Sometimes you might want to make sure if the request has `Origin` header, and is a cors request (a.k.a request method has value equals `OPTION`):

```php
use Drewlabs\Cors\Cors;
use Drewlabs\Cors\ConfigurationBuilder;

$cors = new Cors(
    ConfigurationBuilder::new()
        // Add `Origin: http://localhost` header
        ->withHosts('*')
        // Convert the builded configuration to array
        ->toArray()
);

// Checks if the Psr request is a cors request
$cors->isCorsRequest(new Request());
```
