<?php

namespace Whilesmart\Engagement\Support;

/**
 * One reported figure. A provider returns a list of these; the package never
 * interprets them beyond shipping them to the client, so any host feature can
 * expose a metric without the package knowing what it means.
 */
class Metric
{
    /**
     * @param  array<int, array{date: string, value: float|int}>  $series
     * @param  array<int, array{label: string, value: float|int}>  $rows
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly MetricType $type,
        public readonly float|int|null $value = null,
        public readonly array $series = [],
        public readonly ?string $unit = null,
        public readonly array $rows = [],
    ) {}

    public static function count(string $key, string $label, int $value, ?string $unit = null): self
    {
        return new self($key, $label, MetricType::Count, $value, [], $unit);
    }

    public static function sum(string $key, string $label, float $value, ?string $unit = null): self
    {
        return new self($key, $label, MetricType::Sum, $value, [], $unit);
    }

    public static function ratio(string $key, string $label, float $value): self
    {
        return new self($key, $label, MetricType::Ratio, $value);
    }

    /**
     * @param  array<int, array{date: string, value: float|int}>  $series
     */
    public static function series(string $key, string $label, array $series, ?string $unit = null): self
    {
        return new self($key, $label, MetricType::Series, null, $series, $unit);
    }

    /**
     * @param  array<int, array{label: string, value: float|int}>  $rows
     */
    public static function ranking(string $key, string $label, array $rows): self
    {
        return new self($key, $label, MetricType::Ranking, null, [], null, $rows);
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type->value,
            'value' => $this->value,
            'series' => $this->series,
            'unit' => $this->unit,
            'rows' => $this->rows,
        ];
    }
}
