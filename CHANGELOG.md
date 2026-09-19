# Changelog

## 1.1.0

- Public browser event ingestion with site and origin validation.
- Visitor, session, source, page, and referrer attribution.
- Multiple named clients with isolated origins, campaign attribution, and report filtering.
- Built-in visitor measurements for shared admin reporting.

## 1.0.0

- Initial release.
- Event recording via the `Engagement` facade and the `RecordsEngagement` subject trait.
- `MetricProvider` contract so host features declare the metrics they expose.
- Aggregated report grouping every registered provider's metrics over a period.
- Built-in `EventMetricProvider` (total events, active subjects, active-subjects series, top event names).
- Config-gated `GET {prefix}/engagement/report` endpoint secured by host middleware.
