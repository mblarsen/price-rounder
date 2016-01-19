# PriceRounder

Price rounding lib producing nice prices (pretty prices) with range support. Build for making pretty prices when converting prices from other currencies. 

* Halves: Rounds to nearest half 0.5
* Nines: Rounds least significant digits to 99. E.g. 4.99 or 499
* Ninetyfive (aka 5-cents rounding): Rounds least significant digits to 95. E.g. 4.95, 49.50 or 495
* Range: Lets you configure a price rounder for a specific value range

You can specify a different rounding and formatting scheme for each range. E.g. small prices have decimal rounding to 0.95 while big prices removes the decimals all together.

# Example

Instance a rounder and invoke the round method. That's all:

    $rounder = new NinetyfiveRounder();
    $pretty_price = $rounder->round(15.12); // returns 14.95

Range example:

    $rounder = new RangeRounder([
        10  => "NinesRounder",
        50  => "NinetyfiveRounder",
        150 => "NinetyfiveRounder::1",
        999 => "NinesRounder::2",
        "*" => "round"
    ]);
