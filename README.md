# Whilesmart Eloquent Engagement

Host-agnostic engagement and metrics aggregation for Laravel. Record usage events, and build an admin metrics report from provider classes your own app implements. The package never hard-codes what a "feature" or a "metric" is: your features declare their own.

## Installation

```bash
composer require whilesmart/eloquent-engagement
```

The service provider and the `Engagement` facade are auto-discovered. Publish and run migrations:

```bash
php artisan vendor:publish --tag=engagement-migrations
php artisan migrate
```

## Recording events

The event name is your own vocabulary. Record through the facade:

```php
use Whilesmart\Engagement\Facades\Engagement;

Engagement::record($user, 'transaction.created', ['amount' => 42]);
Engagement::record(null, 'session.start'); // anonymous/system
```

Or add the subject trait to the model events are attributed to:

```php
use Whilesmart\Engagement\Traits\RecordsEngagement;

class User extends Model
{
    use RecordsEngagement;
}

$user->recordEngagement('report.viewed');
```

## Declaring metrics

A metric source implements `MetricProvider`. Each provider owns a group of metrics; the package collects every registered provider and asks it for figures over a period.

```php
use Whilesmart\Engagement\Contracts\MetricProvider;
use Whilesmart\Engagement\Support\Metric;
use Whilesmart\Engagement\Support\Period;

class UsersMetricProvider implements MetricProvider
{
    public function key(): string { return 'users'; }
    public function label(): string { return 'Users'; }

    public function metrics(Period $period): array
    {
        return [
            Metric::count('total_users', 'Total users', User::count()),
            Metric::series('signups', 'New users', /* [{date, value}, ...] */ []),
        ];
    }
}
```

Register providers in `config/engagement.php`:

```php
'providers' => [
    \Whilesmart\Engagement\Providers\EventMetricProvider::class, // built-in, over recorded events
    \App\Engagement\UsersMetricProvider::class,
],
```

or at runtime: `Engagement::registerMetricProvider(UsersMetricProvider::class);`

## Metric shapes

`Metric` value objects carry a `type` so the client knows how to render them:

- `Metric::count($key, $label, $int)`
- `Metric::sum($key, $label, $float)`
- `Metric::ratio($key, $label, $float)` (0..1)
- `Metric::series($key, $label, [['date' => '2026-06-01', 'value' => 12], ...])`

## The report

```php
use Whilesmart\Engagement\Support\Period;
use Whilesmart\Engagement\Facades\Engagement;

$report = Engagement::report(Period::lastDays(30));
```

A config-gated endpoint exposes the same payload:

```
GET {prefix}/engagement/report?days=30&granularity=day
```

Engagement metrics are app-wide, so secure it for admins. Set the middleware in `config/engagement.php`:

```php
'route_middleware' => ['api', 'auth:sanctum', 'role:admin'],
```

Set `'register_routes' => false` to mount your own route instead.

## Built-in provider

`EventMetricProvider` reports total events, distinct active subjects, an active-subjects series, and the top event names from whatever you recorded.

## Configuration

`register_routes`, `route_prefix`, `route_middleware`, `events_table`, and `providers` are all configurable in `config/engagement.php`.

## License

MIT. WhileSmart LTD.
