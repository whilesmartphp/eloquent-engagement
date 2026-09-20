# Changelog

## 1.0.0

- Initial release.
- Event recording via the `Engagement` facade and the `RecordsEngagement` subject trait.
- `MetricProvider` contract so host features declare the metrics they expose.
- `ClientScopedMetricProvider` for providers whose figures a client filter narrows, which
  the report states per group.
- Aggregated report grouping every registered provider's metrics over a period.
- Built-in `EventMetricProvider` (total events, active subjects, active-subjects series, top event names).
- Public browser event ingestion with site and origin validation.
- Visitor, session, source, page, and referrer attribution.
- Multiple named clients with isolated origins, campaign attribution, and report filtering.
- Built-in visitor measurements for shared admin reporting.
- Config-gated report and ingest endpoints, each secured by host middleware.
