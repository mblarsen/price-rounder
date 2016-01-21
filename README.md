# PriceRounder

Price rounding lib producing nice prices (pretty prices) with range support. Build for making pretty prices when converting prices from other currencies.

Ships with a **best price strategy** `BestPriceRounder` that gives the best pretty price for most amounts regardless of size. It can be configured to include _types of nice prices_:

* _9s_ or 1 cent nice prices, like 5.99 or 5.49 `INCLUDE_NINES`
* _95s_ or 5 cent nice prices, like 5.95 `INCLUDE_NINETYFIVES`
* _8s_ for the superstitious, like 498 or 3.48 `INCLUDE_EIGHTS`
* Halves, like 4.5 or 550 `INCLUDE_HALVESINCLUDE_HALVES`

By default 9s, 98s and Halves are included `INCLUDE_DEFAULT`.

In case you want more specific control other `Rounder`s are available as well:

* `HalvesRounder`
* `CentsRounder`
* `NinesRounder` extends `CentsRounder`
* `NinetyfiveRounder` extends `CentsRounder`

These can be combined so that a different rounder is used for different ranges using the `RangeRounder`.

Simple and extensible formatting can be done as well: See `Rounder` class.

# Example

Instance a rounder and invoke the round method. That's all:

    $rounder = new NinetyfiveRounder();
    $rounder->round(15.12); // returns 14.95

Best price example:

    $rounder = new BestPriceRounder();
    $rounder->round(66.33); // return 66.49

    $rounder->printAnalysis(66.33);

    +----------------------+-----------+------+------------+------+-------+---+
    | Candidate for: 66.33 | Base Diff |    % | Total Diff |    % | Score |   |
    +----------------------+-----------+------+------------+------+-------+---+
    |                65.95 |        -1 | -1.5 |      -0.38 | -0.6 |  0.22 |   |
    |                65.99 |        -1 | -1.5 |      -0.34 | -0.5 |  0.18 |   |
    |                66.49 |         0 |  0.0 |       0.16 |  0.2 |  0.04 | * |
    |                 66.5 |         0 |  0.0 |       0.17 |  0.3 |  0.04 |   |
    |                66.95 |         0 |  0.0 |       0.62 |  0.9 |  0.57 |   |
    |                66.99 |         0 |  0.0 |       0.66 |  1.0 |  0.65 |   |
    |                   67 |         1 |  1.5 |       0.67 |  1.0 |  0.67 |   |
    +----------------------+-----------+------+------------+------+-------+---+

    $rounder->printAnalysis(1253.2);

    +-----------------------+-----------+------+------------+------+--------+---+
    | Candidate for: 1253.2 | Base Diff |    % | Total Diff |    % |  Score |   |
    +-----------------------+-----------+------+------------+------+--------+---+
    |                  1195 |       -58 | -4.6 |     -58.20 | -4.9 | 283.45 |   |
    |                  1199 |       -54 | -4.3 |     -54.20 | -4.5 | 245.01 |   |
    |                  1249 |        -4 | -0.3 |      -4.20 | -0.3 |   1.41 |   |
    |                  1250 |        -3 | -0.2 |      -3.20 | -0.3 |   0.82 | * |
    |                  1295 |        42 |  3.4 |      41.80 |  3.2 | 134.92 |   |
    |                  1299 |        46 |  3.7 |      45.80 |  3.5 | 161.48 |   |
    |                  1300 |        47 |  3.8 |      46.80 |  3.6 | 168.48 |   |
    +-----------------------+-----------+------+------------+------+--------+---+

The best value score takes into account the least change in price. By default the price rounder gives edge to odd numbers first and then to gain (or to avoid loss) second. This can be configured when creating the rounder object:

    $rounder = new BestPriceRounder([
        "halves": 3.0,
        "gain": 10.0
    ]);

This will favour values ending in five—4.5 or 450, but first and foremost it will favour prices that does not give a loss.

See more examples in `example.php`:

    +----------+----------+----------+---------+-------------------+
    | Original | No edges | odd: 2.5 | gain: 2 | odd: 2.5, gain: 2 |
    +----------+----------+----------+---------+-------------------+
    | 5.1      | 4.99     | 4.99     | 4.99    | 4.99              |
    | 5.23     | 5.49     | 5.49     | 5.49    | 5.49              |
    | 5.6      | 5.5      | 5.5      | 5.5     | 5.5               |
    | 6.1      | 5.99     | 5.99     | 5.99    | 5.99              |
    | 9.6      | 9.5      | 9.5      | 9.5     | 9.5               |
    | 14.51    | 14.49    | 14.49    | 14.49   | 14.49             |
    | 27.53    | 27.5     | 27.5     | 27.5    | 27.5              |
    | 66.33    | 66.49    | 66.49    | 66.49   | 66.49             |
    | 512      | 499      | 499      | 499     | 499               |
    | 752      | 749      | 749      | 749     | 749               |
    | 1253.2   | 1249     | 1249     | 1249    | 1249              |
    | 2502.33  | 2499     | 2499     | 2499    | 2499              |
    | 2517.33  | 2499     | 2499     | 2499    | 2499              |
    +----------+----------+----------+---------+-------------------+

Range example:

    $rounder = new RangeRounder([
        10  => "NinesRounder",
        50  => "NinetyfiveRounder",
        150 => "NinetyfiveRounder::1",
        999 => "NinesRounder::2",
        "*" => "round"   // uses regular PHP function through function_exists()?
    ]);

    $rounder->round(5.6); // returns 5.99
    $rounder->round(6.1); // returns 5.99
    $rounder->round(9.6); // returns 9.99
    $rounder->round(10.3); // returns 9.95
    $rounder->round(14.49); // returns 13.95
    $rounder->round(14.51); // returns 14.95
    $rounder->round(50); // returns 49.95
    $rounder->round(75); // returns 79.50
    $rounder->round(79); // returns 79.50
    $rounder->round(83); // returns 79.50
    $rounder->round(92); // returns 89.50
    $rounder->round(99); // returns 99.50
    $rounder->round(512); // returns 499
    $rounder->round(752); // returns 799
    $rounder->round(982); // returns 999
    $rounder->round(1100); // returns 1100
    $rounder->round(2222); // returns 2222
    $rounder->round(2502.33); // returns 2502

In the above example the rounders are instansiated based on the strings, but you can use preconfigured instances of rounders as well.

String format:

    Rounder::param[|param2 ...][::[decimals[|dec_point[|thousand_sep]]]]

E.g.:

    "Rounder::::2"    // with value of 5 will output 5.00
    "Rounder::::2|,"  // with value of 5 will output 5,00 // changed decimal point to comma
    "NinetyfiveRounder"    // with value of 92 will output 92.95
    "NinetyfiveRounder::1" // with value of 92 will output 89.5 // changed factor value

Note: Be sure to include namespace if you use your own rounders inherting from `Rounder`.


