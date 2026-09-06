<?php

/**
 * Official CAF Regular Force pension contribution rates for calendar 2026.
 * Reserve Force pension is omitted in v1 — the Reserve plan is separate and
 * 2026 rates were not published on the same Treasury Board table.
 */
return [
    'regular' => [
        'included' => true,
        'calendar_year' => 2026,
        'ympe' => '74600',
        'rate_to_ympe' => '0.0910',
        'rate_above_ympe' => '0.1169',
        'notes' => [
            'Applied to Regular Force base pay only, not to manually entered allowances.',
            'Members who have completed 35 years of pensionable service pay 1% instead. That case is not modeled.',
        ],
    ],
    'reserve' => [
        'included' => false,
        'note' => 'Reserve Force pension contributions are not estimated. The Reserve Force pension plan is separate from the Regular Force plan.',
    ],
];
