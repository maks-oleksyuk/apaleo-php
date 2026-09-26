<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Contract;

use Http\Mock\Client as MockClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Tests\Support\FakeTokenProvider;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * Builds every Request class by reflection, with every argument filled in (nested DTOs too), and
 * sends it through the real pipeline. Then, one argument at a time, it swaps in a different value:
 * the HTTP request must change. An argument that a toArray()/query()/endpoint() drops, misspells
 * into a constant, or maps to the wrong value fails here, without a hand-written test per field.
 *
 * @internal
 */
#[CoversNamespace('Oleksyuk\Apaleo\Resource')]
#[UsesNamespace('Oleksyuk\Apaleo')]
final class RequestMappingTest extends TestCase
{
    /**
     * Arguments that are dropped on purpose, by the end of their path.
     *
     * @var array<string, string> path suffix => why it never reaches the request
     */
    private const array DROPPED = [
        // Read DTOs reused for writing: Apaleo's create models have no such field.
        'externalReferences.legacyId' => 'CreateExternalReferencesModel has no legacyId',
        'companies[0].code' => 'CreateRatePlanCompanyModel takes only id and corporateCode',
        'companies[0].name' => 'CreateRatePlanCompanyModel takes only id and corporateCode',
        'ratePlans[0].code' => 'CreateCompanyRatePlanModel takes only id and corporateCode',
        'ratePlans[0].name' => 'CreateCompanyRatePlanModel takes only id and corporateCode',
        // Sent only for the -daily operations; the baseline builds "export".
        'filter.reference' => 'accounts/export has no reference parameter',
    ];

    private const string BASELINE_DATE = '2026-10-01T12:00:00+02:00';

    private const string OTHER_DATE = '2026-10-02T13:30:00+02:00';

    /** @var null|list<string> every operation in apaleo-api.json, as a regex over "METHOD /path" */
    private static ?array $operations = null;

    /** Path of the one argument that gets its alternative value; null builds the baseline. */
    private ?string $override = null;

    /** Unset optional arguments instead of filling them. */
    private bool $minimal = false;

    /** In minimal mode, still build optional nested DTOs (minimally) rather than leave them out. */
    private bool $nested = false;

    /** @var list<string> every argument path the last build() could vary */
    private array $leaves = [];

    /** @var array<string, int> path => how many shapes it can take (union type members, named constructors) */
    private array $branches = [];

    /** @var array<string, int> path => which shape to build; 0 when absent */
    private array $choices = [];

    /** @param class-string<Request> $class */
    #[DataProvider('provideRequestClasses')]
    public function testEveryArgumentReachesTheRequest(string $class): void
    {
        $ignored = $this->ignoredArguments($class);

        // Every other shape of a union-typed argument or named-constructor DTO, one at a time.
        foreach ($this->branches as $path => $count) {
            for ($choice = 1; $choice < $count; ++$choice) {
                $this->choices = [$path => $choice];
                array_push($ignored, ...$this->ignoredArguments($class));
            }
        }

        self::assertSame([], $ignored, 'Arguments that never reach the HTTP request');
    }

    /** @param class-string<Request> $class */
    #[DataProvider('provideRequestClasses')]
    public function testUnsetOptionalArgumentsAreLeftOut(string $class): void
    {
        $this->minimal = true;

        // Once with optional DTOs left out, once with them built bare, so their own toArray()
        // gets to show it leaves unset fields out too.
        foreach ([false, true] as $nested) {
            $this->nested = $nested;
            $sent = $this->send($this->request($class));

            parse_str($sent->getUri()->getQuery(), $query);
            self::assertNotContains('', $query, 'empty query parameter sent');

            $body = (string) $sent->getBody();
            if ($body !== '') {
                $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
                \assert(\is_array($decoded));
                array_walk_recursive($decoded, static function (mixed $value, int|string $key) use ($class): void {
                    self::assertNotNull($value, "{$class}: \"{$key}\" sent as null");
                });
            }
        }
    }

    /** @return iterable<string, array{class-string<Request>}> */
    public static function provideRequestClasses(): iterable
    {
        $root = \dirname(__DIR__, 2).'/src/Resource';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        $classes = [];
        foreach ($files as $file) {
            Assert::assertInstanceOf(\SplFileInfo::class, $file);
            if (str_ends_with($file->getFilename(), 'Request.php')) {
                $class = 'Oleksyuk\Apaleo\Resource\\'.str_replace('/', '\\', substr($file->getPathname(), \strlen($root) + 1, -4));
                if (is_subclass_of($class, Request::class)) {
                    $classes[] = $class;
                }
            }
        }

        sort($classes);
        foreach ($classes as $class) {
            yield substr($class, \strlen('Oleksyuk\Apaleo\Resource\\')) => [$class];
        }
    }

    /**
     * @param class-string<Request> $class
     *
     * @return list<string>
     */
    private function ignoredArguments(string $class): array
    {
        $this->override = null;
        $request = $this->request($class);
        $leaves = $this->leaves;
        $sent = $this->send($request);
        $this->assertWellFormed($sent);
        $this->assertKnownOperation($sent);
        $baseline = $this->wire($request);

        $ignored = [];
        foreach ($leaves as $leaf) {
            if (array_any(array_keys(self::DROPPED), static fn (string $suffix): bool => str_ends_with($leaf, $suffix))) {
                continue;
            }

            $this->override = $leaf;
            $variant = $this->request($class);
            $this->assertKnownOperation($this->send($variant));
            if ($this->wire($variant) === $baseline) {
                $ignored[] = $leaf;
            }
        }

        return $ignored;
    }

    /**
     * A value that reaches the request under a numeric key instead of its name (a `=>` turned
     * into `>`) still changes the request; so query keys must be names, and body objects must not
     * mix named and numeric keys.
     */
    private function assertWellFormed(RequestInterface $sent): void
    {
        parse_str($sent->getUri()->getQuery(), $query);
        foreach (array_keys($query) as $key) {
            self::assertIsNotNumeric($key, 'query parameter without a name');
        }

        $body = (string) $sent->getBody();
        if ($body === '') {
            return;
        }

        $check = static function (mixed $node) use (&$check): void {
            if (!\is_array($node)) {
                return;
            }

            $numeric = array_filter(array_keys($node), \is_int(...));
            self::assertTrue($numeric === [] || \count($numeric) === \count($node), 'body object mixes named and numeric keys');
            array_walk($node, $check);
        };
        $check(json_decode($body, true, flags: JSON_THROW_ON_ERROR));
    }

    /** The built path must be one Apaleo has, segment for segment (ContractTest reads the source instead). */
    private function assertKnownOperation(RequestInterface $sent): void
    {
        $operations = self::$operations;
        if ($operations === null) {
            $snapshot = json_decode((string) file_get_contents(__DIR__.'/apaleo-api.json'), true, flags: JSON_THROW_ON_ERROR);
            \assert(\is_array($snapshot));
            $operations = array_map(
                static fn (string $operation): string => '#^'.preg_replace('/\\\\\{[^}]+\\\\\}/', '[^/]+', preg_quote($operation, '#')).'$#',
                array_keys($snapshot),
            );
            self::$operations = $operations;
        }

        $operation = $sent->getMethod().' '.$sent->getUri()->getPath();
        self::assertTrue(
            array_any($operations, static fn (string $pattern): bool => preg_match($pattern, $operation) === 1),
            "{$operation} is not an Apaleo operation",
        );
    }

    /** @param class-string<Request> $class */
    private function request(string $class): Request
    {
        $this->leaves = [];
        $this->branches = [];
        $request = $this->object($class, 'request');
        self::assertInstanceOf(Request::class, $request);

        return $request;
    }

    private function wire(Request $request): string
    {
        $sent = $this->send($request);

        return $sent->getMethod().' '.$sent->getUri()."\n".json_encode($sent->getHeaders(), JSON_THROW_ON_ERROR)."\n".$sent->getBody();
    }

    private function send(Request $request): RequestInterface
    {
        $http = new MockClient();
        $http->addResponse(new Response(200, ['Content-Type' => 'application/json'], '{}'));

        $factory = new Psr17Factory();
        new RequestPipeline($http, $factory, $factory, new FakeTokenProvider())->send($request);

        $sent = $http->getLastRequest();
        self::assertInstanceOf(RequestInterface::class, $sent);

        return $sent;
    }

    /** @param class-string $class */
    private function object(string $class, string $path): object
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if ($constructor instanceof \ReflectionMethod && !$constructor->isPublic()) {
            // Named constructors only (e.g. Split::byPercent()/byAmount()): each one is a shape.
            $factories = array_values(array_filter(
                $reflection->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC),
                // PHP 8.4 reports a `self` return type as "self", 8.5 as the class name.
                static fn (\ReflectionMethod $method): bool => $method->isStatic() && \in_array((string) $method->getReturnType(), ['self', $class], true),
            ));
            $factory = $factories[$this->branch($path, \count($factories))];
            $object = $factory->invokeArgs(null, $this->arguments($factory, $reflection, $path));
            \assert(\is_object($object));

            return $object;
        }

        return $constructor instanceof \ReflectionMethod ? $reflection->newInstanceArgs($this->arguments($constructor, $reflection, $path)) : $reflection->newInstance();
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return array<string, mixed>
     */
    private function arguments(\ReflectionMethod $method, \ReflectionClass $class, string $path): array
    {
        preg_match_all('/@param\s+(\S+(?:<[^>]*>)?(?:\|\S+)*)\s+\$(\w+)/', (string) $method->getDocComment(), $docs, PREG_SET_ORDER);
        $docTypes = array_column($docs, 1, 2);

        $arguments = [];
        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();
            $members = $type instanceof \ReflectionUnionType ? $type->getTypes() : [$type];
            $member = $members[$this->branch("{$path}.{$name}:type", \count($members))];
            $native = $member instanceof \ReflectionNamedType ? $member->getName() : 'mixed';
            $docType = $docTypes[$name] ?? null;
            // The doc type refines array/mixed, and narrows a string to its allowed literals.
            $valueType = $docType !== null && (\in_array($native, ['array', 'mixed'], true) || ($native === 'string' && str_contains($docType, "'"))) ? $docType : $native;

            // Minimal: leave out optional values; with $nested, still build nested DTOs bare.
            if ($this->minimal && (!$this->nested || !$this->isDto($valueType, $class))) {
                if ($parameter->isOptional()) {
                    continue;
                }

                if ($type?->allowsNull() === true) {
                    $arguments[$name] = null;

                    continue;
                }
            }

            $arguments[$name] = $this->value($valueType, $class, "{$path}.{$name}");
        }

        return $arguments;
    }

    /** @param \ReflectionClass<object> $context the class whose `use` statements resolve short names */
    private function value(string $type, \ReflectionClass $context, string $path): mixed
    {
        $type = (string) preg_replace('/^\?|^null\||\|null$/', '', $type);

        if (preg_match('/^(?:non-empty-)?(?:list|array)<(?:string,\s*)(.+)>$/', $type, $map) === 1) {
            return ['en' => $this->value($map[1], $context, "{$path}[en]")];
        }

        if (preg_match('/^(?:non-empty-)?(?:list|array)<(?:int,\s*)?(.+)>$/', $type, $list) === 1) {
            $item = $this->value($list[1], $context, "{$path}[0]");

            // An empty list is the other value a list can take (one-value lists have no other).
            return str_starts_with($type, 'non-empty-') ? [$item] : $this->leaf("{$path}[]", [$item], []);
        }

        if (preg_match_all("/'([^']*)'/", $type, $literals) > 0) {
            return $this->leaf($path, ...$literals[1]);
        }

        return match ($type) {
            'string', 'mixed', 'array' => $this->leaf($path, 'x', 'y'),
            'int' => $this->leaf($path, 1, 2),
            'float' => $this->leaf($path, 1.5, 2.5),
            'bool' => $this->leaf($path, true, false),
            default => $this->typed($this->resolve($type, $context), $path),
        };
    }

    private function typed(string $class, string $path): mixed
    {
        if ($class === \DateTimeImmutable::class) {
            $this->leaf($path, self::BASELINE_DATE, self::OTHER_DATE);

            return new \DateTimeImmutable($path === $this->override ? self::OTHER_DATE : self::BASELINE_DATE);
        }

        if ($class === JsonPatch::class) {
            return new JsonPatch()->replace('/x', $this->leaf($path, 'x', 'y'));
        }

        if (enum_exists($class)) {
            $cases = array_values(array_filter(
                $class::cases(),
                static fn (\UnitEnum $case): bool => !$case instanceof \BackedEnum || !str_starts_with((string) $case->value, '__'),
            ));

            return $this->leaf($path, ...$cases);
        }

        \assert(class_exists($class), "unresolvable type {$class} at {$path}");

        return $this->object($class, $path);
    }

    /**
     * One argument the test can vary: the first value is the baseline, the second the alternative.
     * With a single possible value (a one-case enum) there's nothing to vary.
     */
    private function leaf(string $path, mixed ...$values): mixed
    {
        if (\count($values) > 1) {
            $this->leaves[] = $path;
        }

        return $path === $this->override ? $values[1] : $values[0];
    }

    /** @param \ReflectionClass<object> $context */
    private function isDto(string $type, \ReflectionClass $context): bool
    {
        $type = (string) preg_replace('/^\?|^null\||\|null$|^(?:non-empty-)?(?:list|array)<(?:\w+,\s*)?|>$/', '', $type);
        if (\in_array($type, ['string', 'int', 'float', 'bool', 'mixed', 'array'], true) || str_contains($type, "'")) {
            return false;
        }

        $class = $this->resolve($type, $context);

        return class_exists($class) && !\in_array($class, [\DateTimeImmutable::class, JsonPatch::class], true);
    }

    /** Which of $count shapes to build at $path; records the choice point. */
    private function branch(string $path, int $count): int
    {
        if ($count > 1) {
            $this->branches[$path] = $count;
        }

        return $this->choices[$path] ?? 0;
    }

    /**
     * @param \ReflectionClass<object> $context
     */
    private function resolve(string $type, \ReflectionClass $context): string
    {
        $type = ltrim($type, '\\');
        if (class_exists($type) || enum_exists($type)) {
            return $type;
        }

        preg_match_all('/^use ([\w\\\]+?)(?:\\\(\w+))?;$/m', (string) file_get_contents((string) $context->getFileName()), $uses, PREG_SET_ORDER);
        foreach ($uses as $use) {
            if (str_ends_with($use[0], '\\'.$type.';')) {
                return substr($use[0], 4, -1);
            }
        }

        return $context->getNamespaceName().'\\'.$type;
    }
}
