<?php

namespace Whilesmart\Engagement\Support;

enum MetricType: string
{
    /** A single whole-number tally (users, transactions). */
    case Count = 'count';

    /** A single summed quantity (revenue, volume). */
    case Sum = 'sum';

    /** A value between 0 and 1 the client renders as a percentage. */
    case Ratio = 'ratio';

    /** A list of {date, value} points over the period. */
    case Series = 'series';

    /** An ordered leaderboard of {label, value} rows (top users, top features). */
    case Ranking = 'ranking';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
