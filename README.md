# Acme Widget Co [![Build Status](https://travis-ci.org/Antnee/acmewidgetco.svg?branch=master)](https://travis-ci.org/Antnee/acmewidgetco)

## What is this?
This is some example code that I've been asked to write as a small challenge.

The code supports a basket for ordering _widgets_ from the _Acme Widget Co_.

The basket is instantiated with the product catalog (`WidgetRepository`), the
delivery rules (`DeliveryRuleClient`) and an `Offer`.

For the `Widget` support, you will find the `Widget` object itself, the
`WidgetCatalog.json` file which holds a file-system based catalog, a
`WidgetCollection` for handling more than one `Widget`, the `WidgetFsProvider`
which fetches this raw data and returns a simple `array`, the `WidgetProvider`
interface (which is implemented by `WidgetFsProvider`) and the
`WidgetRepository` which would provide full CRUD methods, and is passed into
the basket as described above.

The `DeliveryRule` part of the application supports a runtime configurable
chain of rules that will return as soon as the first rule is met. These rules
support only the basket items pricing at the moment (could be extended to 
support widget weight etc). The basket price is checked and depending on the
rule (ie whether a price range was set, less-than, greater-than, exact-price
etc) then the configured price will be returned.

Discounts are supported via the `Offer` interface. The example given in the
challenge is where the customer buys one ref widget and gets the second for
half price. This is included in the `OfferRedWidgetBulk` class;

## Third party code in use
The collections are an extension of my own
[Collection library](https://packagist.org/packages/antnee/collection), and
currency is handled by [Money](https://packagist.org/packages/moneyphp/money)
to avoid rounding and floating point issues often found with handling monetary
values. I like the Money library so I bundled it here.

## Installing
This package depends on Composer. I have included the `composer.json` and
`composer.lock` files. I have not included the `./vendor` directory. As such,
you'll need to perform a `composer install` command from the root directory of
this project.

## Compatibility
I have targeted PHP 7.2 compatibility. The code may work in PHP 7.0 and 7.1 but
this is by chance and not intentional.

## Tests
Tests are included in `./tests`. I am running these locally with the following
command:

```sh
$ ./vendor/bin/phpunit --testsuite all
```

### Coverage Reports
I have tested with PHP 7.2 locally, and have generated code coverage reports
via PHPUnit and xDebug. These are provided in the `./coverage` directory. The
command to do so is:

```sh
$ ./vendor/bin/phpunit --testsuite all --coverage-html coverage
```

### Testsuites
There are a number of testsuites available if you wish to test only that
particular category:

* `_integration`: These tests use absolutely zero mocks and will test the
    entire application as a whole. This includes the example baskets provided
    with the challenge
* `basket`: Will run the tests on the `Basket`, `BasketItem` and
    `BasketCollection` classes
* `deliveryRules`: Runs the tests against the `DeliveryRule` and `DeliveryRules`
    classed only
* `offers`: Will test the `Offer` interface
* `widget`: Runs the tests for the `Widget`, `WidgetCollection`,
    `WidgetFsProvider` and `WidgetRepository` 

## Assumptions
While writing this, I have made the following assumptions:

1. All widgets are priced in US$. No other currency is acceptable
2. While it is possible to `add()` a negative quantity of widgets to reduce the
    total in your basket, it is not possible to have the end quantity be
    negative. Setting a quantity of 0 (either by adding the negative of the
    actual quantity, or by updating to 0) will do the same thing as calling the
    `remove()` method, ie just take that item out of your basket
3. You can only take advantage of one offer at a time. The first offer that
    matches is the one that you get. I haven't added any logic to decide which
    offer is better for the customer or the business
4. There is no default delivery rule. You MUST specify a rule that meets every
    possible price, even if that's the `DeliveryRule::basketPriceAnything()`
    method. This is to ensure that there are no assumptions made by the code
    itself
5. Where offers are applied, prices are rounded normally. I had originally
    rounded the discount DOWN, so that the end price was at most a cent more
    expensive than the other way around. However, this decision meant that the
    test cases that I'd been given failed. Using normal rounding (ie <5 rounds
    down, >=5 rounds up) fixed this issue. It seems fair to assume that this is
    the intended behaviour
