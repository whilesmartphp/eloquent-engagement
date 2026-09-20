<?php

namespace Whilesmart\Engagement\Support;

use InvalidArgumentException;

class ClientRegistry
{
    public function all(): array
    {
        return collect(config('engagement.clients', []))
            ->map(function (mixed $client, string $key): array {
                if (! is_array($client) || ! isset($client['name'], $client['site_key'], $client['allowed_origins'])) {
                    throw new InvalidArgumentException("Engagement client [{$key}] is not configured correctly.");
                }

                return [
                    'key' => $key,
                    'name' => (string) $client['name'],
                    'site_key' => (string) $client['site_key'],
                    'allowed_origins' => array_values((array) $client['allowed_origins']),
                ];
            })
            ->all();
    }

    public function publicClients(): array
    {
        return array_values(array_map(
            fn (array $client) => ['key' => $client['key'], 'name' => $client['name']],
            $this->all()
        ));
    }

    public function has(string $key): bool
    {
        return isset($this->all()[$key]);
    }

    public function matching(string $siteKey, ?string $origin): ?array
    {
        foreach ($this->all() as $client) {
            if ($client['site_key'] !== ''
                && hash_equals($client['site_key'], $siteKey)
                && is_string($origin)
                && in_array($origin, $client['allowed_origins'], true)) {
                return $client;
            }
        }

        return null;
    }
}
