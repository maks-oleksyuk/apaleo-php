<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Contract;

use PHPUnit\Framework\TestCase;

/**
 * Checks every Request class against apaleo-api.json, a snapshot of Apaleo's OpenAPI specs
 * (refresh it with update-snapshot.php): the endpoint exists, every query parameter is one
 * Apaleo knows, required ones are sent, and Idempotency-Key is accepted wherever Apaleo does.
 *
 * Reads the Request sources rather than instantiating them, so it needs no fixture per class.
 *
 * @internal
 *
 * @coversNothing
 */
final class ContractTest extends TestCase
{
    /** POSTs that only read (export/aggregate): an idempotency key would add nothing. */
    private const array READ_ONLY_POSTS = ['TransactionsRequest'];

    /** @var null|array<string, array{query: array<string, bool>, idempotencyKey: bool}> */
    private static ?array $spec = null;

    /** @var null|list<array{class: string, operations: list<string>, query: list<string>, idempotencyKey: bool}> */
    private static ?array $requests = null;

    public function testEveryRequestTargetsAnOperationApaleoHas(): void
    {
        $offenders = [];
        foreach ($this->requests() as $request) {
            if ($request['operations'] === []) {
                $offenders[] = $request['class'];
            }
        }

        self::assertSame([], $offenders, 'Endpoint/method not found in apaleo-api.json');
    }

    public function testQueryParametersMatchTheSpec(): void
    {
        $offenders = [];
        foreach ($this->requests() as $request) {
            // A request can match several operations (a runtime path segment): a parameter is
            // known if any of them has it, and required only if all of them require it.
            $known = [];
            $required = null;
            foreach ($request['operations'] as $operation) {
                $query = self::spec()[$operation]['query'];
                $known += $query;
                $requiredHere = array_keys(array_filter($query));
                $required = $required === null ? $requiredHere : array_intersect($required, $requiredHere);
            }

            foreach ($request['query'] as $name) {
                if (!isset($known[$name])) {
                    $offenders[] = "{$request['class']}: unknown query parameter \"{$name}\"";
                }
            }

            foreach ($required ?? [] as $name) {
                if (!\in_array($name, $request['query'], true)) {
                    $offenders[] = "{$request['class']}: required query parameter \"{$name}\" is never sent";
                }
            }
        }

        self::assertSame([], $offenders);
    }

    public function testIdempotencyKeyIsAcceptedWhereverApaleoSupportsIt(): void
    {
        $offenders = [];
        foreach ($this->requests() as $request) {
            if ($request['idempotencyKey'] || \in_array($request['class'], self::READ_ONLY_POSTS, true)) {
                continue;
            }

            foreach ($request['operations'] as $operation) {
                if (self::spec()[$operation]['idempotencyKey']) {
                    $offenders[] = "{$request['class']} ({$operation})";
                }
            }
        }

        self::assertSame([], $offenders);
    }

    /** @return array<string, array{query: array<string, bool>, idempotencyKey: bool}> */
    private static function spec(): array
    {
        if (self::$spec === null) {
            /** @var array<string, array{query: array<string, bool>, idempotencyKey: bool}> $spec */
            $spec = json_decode((string) file_get_contents(__DIR__.'/apaleo-api.json'), true, flags: JSON_THROW_ON_ERROR);
            self::$spec = $spec;
        }

        return self::$spec;
    }

    /** @return list<array{class: string, operations: list<string>, query: list<string>, idempotencyKey: bool}> */
    private function requests(): array
    {
        if (self::$requests !== null) {
            return self::$requests;
        }

        $sources = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__.'/../../src/Resource', \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            self::assertInstanceOf(\SplFileInfo::class, $file);
            if ($file->getExtension() === 'php') {
                $sources[$file->getBasename('.php')] = (string) file_get_contents($file->getPathname());
            }
        }

        self::$requests = [];
        foreach ($sources as $class => $source) {
            $endpoint = $this->body($source, 'endpoint');
            if ($endpoint === null) {
                continue;
            }

            preg_match_all('/Method::([A-Z]+)/', (string) $this->body($source, 'method'), $methods);
            $operations = [];
            foreach ($methods[1] as $method) {
                foreach ($this->paths($endpoint) as $path) {
                    array_push($operations, ...$this->matchingOperations($method, $path));
                }
            }

            $query = $this->arrayKeys($this->body($source, 'query'));
            if (preg_match_all('/private (\w+Filter) \$\w+/', $source, $filters) > 0) {
                foreach ($filters[1] as $filter) {
                    array_push($query, ...$this->arrayKeys($this->body($sources[$filter] ?? '', 'toQuery')));
                }
            }

            self::$requests[] = [
                'class' => $class,
                'operations' => array_values(array_unique($operations)),
                'query' => array_values(array_unique($query)),
                'idempotencyKey' => str_contains($source, 'Idempotency-Key'),
            ];
        }

        return self::$requests;
    }

    private function body(string $source, string $function): ?string
    {
        return preg_match('/function '.$function.'\([^)]*\): \??\w+\s*\{(.*?)\n    \}/s', $source, $match) === 1 ? $match[1] : null;
    }

    /**
     * Turns an endpoint() body into its possible paths: rawurlencode(...) becomes "{}" (a path
     * parameter), a bare $this->x becomes "*" (a literal segment chosen at runtime, e.g. an action
     * name), and suffix literals ('/$force', match arms like '/by-link') are appended to the base.
     *
     * @return list<string>
     */
    private function paths(string $endpoint): array
    {
        $endpoint = (string) preg_replace("/'\\s*\\.\\s*rawurlencode\\([^)]*\\)\\s*(\\.\\s*'|;)/", '{}\'$1', $endpoint);
        $endpoint = (string) preg_replace("/'\\s*\\.\\s*\\\$this->\\w+\\s*(\\.\\s*'|;)/", '*\'$1', $endpoint);
        $endpoint = str_replace(["'.'", "';"], ['', "'"], $endpoint);
        preg_match_all("/'([^']*)'/", $endpoint, $literals);

        $bases = array_values(array_filter($literals[1], static fn (string $literal): bool => preg_match('#^/\w+/v\d+/#', $literal) === 1));
        $suffixes = array_values(array_diff($literals[1], $bases));

        $paths = $bases;
        foreach ($bases as $base) {
            foreach ($suffixes as $suffix) {
                $paths[] = $base.$suffix;
            }
        }

        return $paths;
    }

    /** @return list<string> */
    private function matchingOperations(string $method, string $path): array
    {
        $segments = explode('/', $path);
        $matches = [];
        foreach (array_keys(self::spec()) as $operation) {
            [$specMethod, $specPath] = explode(' ', $operation, 2);
            $specSegments = explode('/', (string) preg_replace('/\{[^}]+\}/', '{}', $specPath));
            if ($specMethod !== $method || \count($specSegments) !== \count($segments)) {
                continue;
            }

            foreach ($segments as $i => $segment) {
                if ($segment !== '*' && $segment !== $specSegments[$i]) {
                    continue 2;
                }
            }

            $matches[] = $operation;
        }

        return $matches;
    }

    /** @return list<string> */
    private function arrayKeys(?string $body): array
    {
        preg_match_all("/'(\\w+)' =>/", (string) $body, $keys);

        return $keys[1];
    }
}
