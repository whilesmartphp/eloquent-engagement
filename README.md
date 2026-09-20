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

## Browser events

The browser endpoint accepts batches from `@whilesmart/engagement`:

```
POST {prefix}/engagement/events
```

Configure named clients with their public site identifiers and exact browser origins:

```php
'clients' => [
    'dashboard' => [
        'name' => 'Dashboard',
        'site_key' => env('ENGAGEMENT_DASHBOARD_SITE_KEY', ''),
        'allowed_origins' => ['https://app.example.com'],
    ],
    'website' => [
        'name' => 'Website',
        'site_key' => env('ENGAGEMENT_WEBSITE_SITE_KEY', ''),
        'allowed_origins' => ['https://www.example.com'],
    ],
],
```

Each site key identifies one client and is safe to expose in browser code. Origin validation and rate limiting protect the endpoint. Browser events capture UTM attribution and common advertising click identifiers. Reports accept `?client=website`, while an omitted client combines all clients. Set `ENGAGEMENT_REGISTER_INGEST_ROUTE=false` when browser collection is not required.

## Built-in provider

`EventMetricProvider` reports total events, distinct active subjects, an active-subjects series, and the top event names from whatever you recorded. `VisitorMetricProvider` reports unique visitors, sessions, page views, visitor trends, sources, and pages from browser events.

## Configuration

Report and ingestion routes, their middleware, the route prefix, site identity, allowed origins, batch size, events table, and metric providers are configurable in `config/engagement.php`.

## License

MIT. WhileSmart LTD.
