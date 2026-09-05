# Tax calculation fixtures

Expected values in these tests are calculated from CRA T4127 Option 1
(full-year employee, constant pay, claim code 1) using the 2026 rule files
in `resources/tax/2026`.

When a yearly tax update changes a rate, threshold, or credit:

1. Update the versioned files in `resources/tax/{year}`.
2. Run the suite. Failures should name the amount that changed.
3. Recalculate the fixture from the official source. Do not loosen assertions.

The annual method used here can differ from a live per-period payroll run by
a few cents because CRA prorates the $3,500 CPP exemption per pay period
using truncated table amounts (T4127 Table 6.1).

## Worked cases

See `tests/Unit/Tax/PayrollCalculatorTest.php` for independently computed
dollar amounts (not copied from the calculator’s own output).
