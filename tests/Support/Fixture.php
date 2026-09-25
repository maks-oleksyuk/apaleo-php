<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Tests\Support;

/**
 * Builds sample data from constructor signatures, so tests need no hand-written fixture per DTO/Request:
 * `response()` is the decoded-JSON shape a DTO::fromArray() reads, `object()` is a constructed instance.
 * Field names in API responses match constructor parameter names throughout the library.
 */
final class Fixture
{
    private const string OMIT = '__omit__';

    /** @var list<class-string> classes being built, to cut self-referencing DTOs (account trees) */
    private static array $stack = [];

    /**
     * Every class living in a `$directory` folder (DTO, Requests, Enum) under src/Resource.
     *
     * @return array<string, array{class-string}> keyed by short class path, ready for a data provider
     */
    public static function classesIn(string $directory): array
    {
        $root = \dirname(__DIR__, 2).'/src/';
        $classes = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.'Resource', \FilesystemIterator::SKIP_DOTS)) as $file) {
            \assert($file instanceof \SplFileInfo);
            if ($file->getExtension() !== 'php' || basename($file->getPath()) !== $directory) {
                continue;
            }

            $name = str_replace(['/', '.php'], ['\\', ''], substr($file->getPathname(), \strlen($root)));

            /** @var class-string $class */
            $class = 'Oleksyuk\Apaleo\\'.$name;
            $classes[substr($name, \strlen('Resource\\'))] = [$class];
        }

        ksort($classes);

        return $classes;
    }

    /**
     * @param class-string $class
     *
     * @return array<string, mixed>
     */
    public static function response(string $class): array
    {
        $data = [];
        self::$stack[] = $class;
        foreach (self::parameters($class) as $parameter) {
            $value = self::value($parameter, $class, true);
            if ($value !== self::OMIT) {
                $data[$parameter->getName()] = $value;
            }
        }

        array_pop(self::$stack);

        return $data;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public static function object(string $class): object
    {
        $constructor = new \ReflectionClass($class)->getConstructor();
        if ($constructor?->isPrivate()) {
            return self::viaFactory($class);
        }

        $args = [];
        foreach (self::parameters($class) as $parameter) {
            $value = self::value($parameter, $class, false);
            $args[$parameter->getName()] = $value === self::OMIT ? null : $value;
        }

        return new $class(...$args);
    }

    /**
     * Value objects with a private constructor are built through their first public static factory.
     *
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private static function viaFactory(string $class): object
    {
        foreach (new \ReflectionClass($class)->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC) as $factory) {
            $args = array_map(static fn (\ReflectionParameter $parameter): mixed => self::value($parameter, $class, false), $factory->getParameters());

            $object = $factory->invoke(null, ...$args);
            \assert($object instanceof $class);

            return $object;
        }

        throw new \LogicException($class.' has a private constructor and no public factory.');
    }

    /**
     * @param class-string $class
     *
     * @return list<\ReflectionParameter>
     */
    private static function parameters(string $class): array
    {
        return new \ReflectionClass($class)->getConstructor()?->getParameters() ?? [];
    }

    /** @param class-string $class */
    private static function value(\ReflectionParameter $parameter, string $class, bool $asResponse): mixed
    {
        $type = $parameter->getType();
        if ($type instanceof \ReflectionUnionType) {
            $type = $type->getTypes()[0];
        }

        \assert($type instanceof \ReflectionNamedType);

        return match ($type->getName()) {
            'string' => 'x',
            'int' => 1,
            'float' => 1.5,
            'bool' => true,
            'array' => self::arrayOf($parameter, $class, $asResponse),
            default => self::typed($type->getName(), $asResponse, self::readsDateOnly($class, $parameter->getName())),
        };
    }

    private static function typed(string $name, bool $asResponse, bool $dateOnly): mixed
    {
        if (is_a($name, \DateTimeInterface::class, true)) {
            $date = new \DateTimeImmutable('2026-01-15T10:00:00+00:00');

            return $asResponse ? $date->format($dateOnly ? 'Y-m-d' : DATE_ATOM) : $date;
        }

        if (enum_exists($name)) {
            \assert(is_subclass_of($name, \BackedEnum::class));
            $case = array_values(array_filter($name::cases(), static fn (\BackedEnum $case): bool => !str_starts_with((string) $case->value, '__')))[0];

            return $asResponse ? $case->value : $case;
        }

        \assert(class_exists($name));
        if ($asResponse && \in_array($name, self::$stack, true)) {
            return self::OMIT;
        }

        return $asResponse ? self::response($name) : self::object($name);
    }

    /**
     * @param class-string $class
     *
     * @return array<array-key, mixed>
     */
    private static function arrayOf(\ReflectionParameter $parameter, string $class, bool $asResponse): array
    {
        $doc = (string) new \ReflectionClass($class)->getConstructor()?->getDocComment();
        if (preg_match('/@param\s+\??(?:array|list)<(?:(string|int),\s*)?([\\\\\w]+)>\s+\$'.$parameter->getName().'\b/', $doc, $match) !== 1) {
            return ['x'];
        }

        $keyed = $match[1] !== '';
        if ($asResponse && \in_array(self::resolve($class, $match[2]), self::$stack, true)) {
            return [];
        }

        $item = match ($match[2]) {
            'string', 'mixed' => 'x',
            'int' => 1,
            'float' => 1.5,
            'bool' => true,
            default => self::typed(self::resolve($class, $match[2]), $asResponse, false),
        };

        return $keyed ? ['en' => $item] : [$item];
    }

    /**
     * Date-only fields ("2026-01-15") are told apart from date-times by the ResponseData call that reads them.
     *
     * @param class-string $class
     */
    private static function readsDateOnly(string $class, string $field): bool
    {
        $source = (string) file_get_contents((string) new \ReflectionClass($class)->getFileName());

        return preg_match('/ResponseData::(?:nullableDate|date)\(\$data, \''.$field."'/", $source) === 1;
    }

    /**
     * @param class-string $class
     */
    private static function resolve(string $class, string $short): string
    {
        if (\in_array($short, ['self', 'static'], true)) {
            return $class;
        }

        $reflection = new \ReflectionClass($class);
        $source = (string) file_get_contents((string) $reflection->getFileName());
        if (preg_match('/^use ([\\\\\w]+\\\\'.preg_quote($short, '/').');/m', $source, $match) === 1) {
            return $match[1];
        }

        return $reflection->getNamespaceName().'\\'.$short;
    }
}
