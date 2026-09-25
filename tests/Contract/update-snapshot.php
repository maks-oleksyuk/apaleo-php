<?php

declare(strict_types=1);

/*
 * Refreshes apaleo-api.json from Apaleo's live OpenAPI specs: `php tests/Contract/update-snapshot.php`.
 * Only what ContractTest checks is kept (operations, query parameters, Idempotency-Key support),
 * so the diff of a refresh shows exactly what Apaleo added, removed or renamed.
 */

const APIS = ['account', 'availability', 'booking', 'finance', 'inventory', 'logs', 'operations', 'rateplan', 'reports', 'settings'];
const METHODS = ['get', 'post', 'put', 'patch', 'delete', 'head'];

$snapshot = [];
foreach (APIS as $api) {
    $json = file_get_contents("https://api.apaleo.com/swagger/{$api}-v1/swagger.json");
    $spec = json_decode((string) $json, true, flags: JSON_THROW_ON_ERROR);
    if (!is_array($spec) || !is_array($spec['paths'] ?? null)) {
        throw new RuntimeException("Unexpected spec shape for {$api}.");
    }

    $sharedParameters = is_array($spec['parameters'] ?? null) ? $spec['parameters'] : [];

    foreach ($spec['paths'] as $path => $operations) {
        foreach ((array) $operations as $method => $operation) {
            if (!in_array($method, METHODS, true) || !is_array($operation)) {
                continue;
            }

            $query = [];
            $idempotencyKey = false;
            foreach ((array) ($operation['parameters'] ?? []) as $parameter) {
                if (is_array($parameter) && is_string($parameter['$ref'] ?? null)) {
                    $parameter = $sharedParameters[basename($parameter['$ref'])] ?? [];
                }

                if (!is_array($parameter) || !is_string($parameter['name'] ?? null)) {
                    continue;
                }

                if (($parameter['in'] ?? null) === 'query') {
                    $query[$parameter['name']] = ($parameter['required'] ?? false) === true;
                }

                if (($parameter['in'] ?? null) === 'header' && strtolower($parameter['name']) === 'idempotency-key') {
                    $idempotencyKey = true;
                }
            }

            ksort($query);

            $snapshot[strtoupper($method).' '.$path] = ['query' => (object) $query, 'idempotencyKey' => $idempotencyKey];
        }
    }
}

ksort($snapshot);

file_put_contents(__DIR__.'/apaleo-api.json', json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
echo count($snapshot)." operations written.\n";
