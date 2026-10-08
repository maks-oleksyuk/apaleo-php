<div align="center">

# apaleo-php

[![CI](https://img.shields.io/github/actions/workflow/status/maks-oleksyuk/apaleo-php/ci.yml?branch=main&style=flat&label=CI)](//github.com/maks-oleksyuk/apaleo-php/actions/workflows/ci.yml)
[![Coverage](https://img.shields.io/codecov/c/github/maks-oleksyuk/apaleo-php?style=flat&logo=codecov&logoColor=white)](//codecov.io/gh/maks-oleksyuk/apaleo-php)
[![Latest Version](https://img.shields.io/packagist/v/oleksyuk/apaleo-php.svg?style=flat&logo=packagist&logoColor=white&color=F28D1A)](//packagist.org/packages/oleksyuk/apaleo-php)
[![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-777bb4?style=flat&logo=php&logoColor=white)](composer.json)
[![Total Downloads](https://img.shields.io/packagist/dt/oleksyuk/apaleo-php.svg?style=flat&logo=packagist&logoColor=white&color=F28D1A)](//packagist.org/packages/oleksyuk/apaleo-php/stats)

Framework-agnostic PHP SDK for the [Apaleo](//apaleo.com) hotel PMS API.

</div>

## Installation

```bash
composer require oleksyuk/apaleo-php
```

Bring any [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client and [PSR-17](https://www.php-fig.org/psr/psr-17/) factories, e.g. `symfony/http-client` + `nyholm/psr7`, or `guzzlehttp/guzzle`.

## Usage

```php
use Oleksyuk\Apaleo\ApaleoClient;
use Oleksyuk\Apaleo\Auth\ClientCredentialsTokenProvider;
use Symfony\Component\HttpClient\Psr18Client;

$http = new Psr18Client(); // also serves as the PSR-17 request/stream factory

$tokenProvider = new ClientCredentialsTokenProvider($http, $http, $http, 'your-client-id', 'your-client-secret');
$apaleo = new ApaleoClient($http, $http, $http, $tokenProvider);

$properties = $apaleo->inventory()->properties()->list();

foreach ($properties as $property) {
    echo $property->id, ' — ', implode(', ', $property->name), "\n";
}
```

With Guzzle, pass `new GuzzleHttp\Client()` as the client and `new GuzzleHttp\Psr7\HttpFactory()` as both factories. Inside a DI container, wire the same services the container already has.

### Timeouts and retries

The SDK doesn't retry on its own: that's the HTTP client's job. With `symfony/http-client`, this matches what `apaleo-bundle` sets up: a 10 s timeout, and `5xx`/transport errors retried only for `GET`/`HEAD`, because a `502` after a `POST` may still have been applied. `429` is deliberately not retried there: the client would sleep for whatever `Retry-After` says, uncapped. Catch `ApaleoRateLimitException` and use its `retryAfterSeconds` to decide whether to wait.

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
    new GenericRetryStrategy([0 => $safe, 500 => $safe, 502 => $safe, 503 => $safe, 504 => $safe]),
    maxRetries: 2,
);
$psr18 = new Psr18Client($http); // also serves as the PSR-17 request/stream factory

$tokenProvider = new ClientCredentialsTokenProvider($psr18, $psr18, $psr18, 'your-client-id', 'your-client-secret');
$apaleo = new ApaleoClient($psr18, $psr18, $psr18, $tokenProvider, asyncHttpClient: $http);
```

Passing `asyncHttpClient` also lets `sendMany()` run its requests concurrently. With Guzzle, use its `Middleware::retry()` with the same rules.

See [`apaleo-bundle`](//github.com/maks-oleksyuk/apaleo-bundle) for a ready-made Symfony integration (autowired service, cached token, HTTP client with this timeout and retry policy).

### Token cache

By default the access token is kept in memory, which under PHP-FPM means a new token request on every HTTP request. Share it through any PSR-16 cache (needs `psr/simple-cache`, which PSR-16 implementations such as `symfony/cache` don't always pull in):

```php
use Oleksyuk\Apaleo\Auth\Psr16TokenCache;

$tokenProvider = new ClientCredentialsTokenProvider($http, $http, $http, 'your-client-id', 'your-client-secret', new Psr16TokenCache($psr16Cache));
```

Or implement `TokenCacheInterface` on top of your framework's own storage.

On a `401` the SDK drops the cached token, fetches a new one and retries the request at once.

### Errors

Everything the SDK throws implements `ApaleoExceptionInterface`, so one `catch` covers it:

| Exception                           | When                                                                         |
|-------------------------------------|------------------------------------------------------------------------------|
| `ApaleoValidationException`         | `400` / `422`, with `messages`                                               |
| `ApaleoAuthException`               | `401` / `403`                                                                |
| `ApaleoNotFoundException`           | `404`                                                                        |
| `ApaleoRateLimitException`          | `429`, with `Retry-After`                                                    |
| `ApaleoServerException`             | `5xx`, including HTML bodies from a proxy                                    |
| `ApaleoTransportException`          | timeout, DNS, connection refused: no HTTP status, `statusCode` doesn't exist |
| `ApaleoUnexpectedResponseException` | a `2xx` with a non-JSON or malformed body                                    |

The HTTP ones extend `ApaleoException` and carry `statusCode` (also returned by `getCode()`), `apaleoErrorType` and `rawResponse`. Invalid arguments (an `Unknown` enum value in a filter, a body that can't be JSON-encoded) throw `\InvalidArgumentException` before anything is sent.

### Pagination

`list()` returns a `PaginatedResult` (`items`, `totalCount`). `pageSize` is at most 500. To walk every page lazily:

```php
use Oleksyuk\Apaleo\Support\Paginator;

foreach (Paginator::all(fn (int $page) => $apaleo->inventory()->units()->list(pageNumber: $page, pageSize: 500)) as $unit) {
    // ...
}
```

Pages are offsets, so don't change what the filter matches while walking them: if you archive or re-status items as you go, the next page shifts and skips some. Collect the IDs first, then act on them.

### Idempotency

Every method that creates something (bookings, reservations, authorizations, payments, charges, folios, blocks, groups, inventory, rate plans, settings...) accepts an optional `$idempotencyKey`. Pass the same key when retrying a `POST` after a timeout, so Apaleo doesn't apply it twice: without one, a retried authorization can charge a guest's card twice.

```php
$key = bin2hex(random_bytes(16)); // generate once per logical operation, reuse on retry
$apaleo->booking()->authorizations()->createByTerminal($target, $amount, $terminalId, idempotencyKey: $key);
```

### Requests the SDK doesn't cover

`send()` and `sendMany()` take low-level `Request` objects, either SDK ones or your own subclass, and return the decoded body:

```php
use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetThingRequest extends Request
{
    public function __construct(private string $id) {}

    public function method(): Method { return Method::GET; }

    public function endpoint(): string { return '/some/v1/things/'.rawurlencode($this->id); }
}

$data = $apaleo->send(new GetThingRequest('X'));
```

For a binary endpoint (PDF, CSV), use `sendRaw()`: it returns the body as-is, while errors still map to the usual exceptions. Override `Request::accept()` with the media type you expect.

`sendMany()` runs the requests concurrently (see `asyncHttpClient` above) and is all-or-nothing: if one fails, its exception is thrown and the other results are lost.

### Good to know

- **Unrecognized enum values.** Every response enum has an `Unrecognized` case, so a value Apaleo adds later doesn't break parsing. You can't send it back: filters reject it.
- **Shared types.** Types that are identical in every API that uses them (`MonetaryValue`, `EmbeddedProperty`, `ChannelCode`, `UnitGroupType`, ...) live once in `Oleksyuk\Apaleo\Resource\Shared`, so a value read from one API can be passed straight to another. Types whose shape differs per API (e.g. `GuaranteeType`) stay in that API's namespace.
- **Strings are trimmed.** Many fields are typed in by hotel staff and carry stray whitespace. Optional strings that end up empty become `null`.
- **Dates.** Date-only fields (`serviceDate`, `arrival` in offers, ...) are midnight UTC `DateTimeImmutable`s, so `format('Y-m-d')` gives back the same day in any `date.timezone`. Date-time fields keep Apaleo's offset.
- **Money is a `float`.** JSON has already lost precision by the time the SDK sees it. Don't sum `amount` values directly: round with `round($x, 2)` or convert to minor units first.

## Development

```bash
task lint            # PHPStan (max level), PHP CS Fixer, Rector, composer validate
task test            # PHPUnit
task test:coverage   # PHPUnit + coverage report in build/ (needs Xdebug or PCOV)
task fix             # auto-fix CS Fixer / Rector issues
```
