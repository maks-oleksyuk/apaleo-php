<div align="center">

# apaleo-php

[![CI](https://img.shields.io/github/actions/workflow/status/maks-oleksyuk/apaleo-php/ci.yml?branch=main&style=flat&label=CI)](//github.com/maks-oleksyuk/apaleo-php/actions/workflows/ci.yml)
[![Latest Version](https://img.shields.io/packagist/v/oleksyuk/apaleo-php.svg?style=flat&logo=packagist&logoColor=white&color=F28D1A)](//packagist.org/packages/oleksyuk/apaleo-php)
[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-777bb4?style=flat&logo=php&logoColor=white)](composer.json)
[![Total Downloads](https://img.shields.io/packagist/dt/oleksyuk/apaleo-php.svg?style=flat&logo=packagist&logoColor=white&color=F28D1A)](//packagist.org/packages/oleksyuk/apaleo-php/stats)

Framework-agnostic PHP SDK for the [Apaleo](//apaleo.com) hotel PMS API.

</div>

## Installation

```bash
composer require oleksyuk/apaleo-php
```

Requires an actual [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client (e.g. `symfony/http-client`, `guzzlehttp/guzzle`) to be installed — [`php-http/discovery`](https://github.com/php-http/discovery) picks it up automatically, it doesn't invent one.

## Usage

```php
use Oleksyuk\Apaleo\ApaleoClient;

$apaleo = ApaleoClient::create(
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret',
);

$properties = $apaleo->inventory()->properties()->list();

foreach ($properties as $property) {
    echo $property->id, ' — ', implode(', ', $property->name), "\n";
}
```

`ApaleoClient::create()` auto-discovers a PSR-18 client and PSR-17 factories for you. To wire your own instead (e.g. inside a DI container), use the constructor directly:

```php
use Oleksyuk\Apaleo\ApaleoClient;
use Oleksyuk\Apaleo\Auth\ClientCredentialsTokenProvider;

$tokenProvider = new ClientCredentialsTokenProvider(
    $httpClient,
    $requestFactory,
    $streamFactory,
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret',
);

$apaleo = new ApaleoClient($httpClient, $requestFactory, $streamFactory, $tokenProvider);
```

### Timeouts and retries

The SDK doesn't retry on its own: that's the HTTP client's job. With `symfony/http-client`, this matches what `apaleo-bundle` sets up: a 10 s timeout, `429` retried for every method (honoring `Retry-After`), and `5xx`/transport errors retried only for `GET`/`HEAD`, because a `502` after a `POST` may still have been applied.

```php
use Oleksyuk\Apaleo\ApaleoClient;
use Oleksyuk\Apaleo\Auth\ClientCredentialsTokenProvider;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Retry\GenericRetryStrategy;
use Symfony\Component\HttpClient\RetryableHttpClient;

$safe = ['GET', 'HEAD'];
$http = new RetryableHttpClient(
    HttpClient::create(['timeout' => 10]),
    new GenericRetryStrategy([0 => $safe, 429, 500 => $safe, 502 => $safe, 503 => $safe, 504 => $safe]),
    maxRetries: 2,
);
$psr18 = new Psr18Client($http); // also serves as the PSR-17 request/stream factory

$tokenProvider = new ClientCredentialsTokenProvider($psr18, $psr18, $psr18, 'your-client-id', 'your-client-secret');
$apaleo = new ApaleoClient($psr18, $psr18, $psr18, $tokenProvider, asyncHttpClient: $http);
```

Passing `asyncHttpClient` also lets `sendMany()` run its requests concurrently. With Guzzle, use its `Middleware::retry()` with the same rules.

See [`apaleo-bundle`](//github.com/maks-oleksyuk/apaleo-bundle) for a ready-made Symfony integration (autowired service, cached token, HTTP client with this timeout and retry policy).

## Development

```bash
task lint   # PHPStan (max level), PHP CS Fixer, Rector, composer validate
task test   # PHPUnit
task fix    # auto-fix CS Fixer / Rector issues
```
