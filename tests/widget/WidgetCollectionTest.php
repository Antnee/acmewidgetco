<?php
namespace Tests;

use App\Widget;
use App\WidgetCollection;
use Money\Money;
use PHPUnit\Framework\TestCase;

class WidgetCollectionTest extends TestCase
{
    /**
     * @test
     * @dataProvider validWidgetDataProvider
     * @param Widget ...$widgets
     */
    public function collectWidgets(Widget ...$widgets)
    {
        $collection = collectInto(WidgetCollection::class, $widgets);
        self::assertCount(count($widgets), $collection);
    }

    /**
     * @test
     * @dataProvider validWidgetDataProvider
     * @param Widget ...$widgets
     */
    public function appendWidgets(Widget ...$widgets)
    {
        $collection = new WidgetCollection;
        $count = 0;
        foreach ($widgets as $widget) {
            $collection[] = $widget;
            $count++;
            $this->assertCount($count, $collection);
        }
    }

    /**
     * @test
     * @dataProvider invalidWidgetDataProvider
     * @expectedException \TypeError
     * @param $invalidThing
     */
    public function testOffsetSetInvalidWidget($invalidThing)
    {
        $collection = new WidgetCollection;
        $collection[] = $invalidThing;
    }

    /**
     * @test
     * @dataProvider invalidWidgetDataProvider
     * @expectedException \TypeError
     * @param $invalidThing
     */
    public function testAppendingInvalidWidget($invalidThing)
    {
        $collection = new WidgetCollection;
        $collection->append($invalidThing);
    }

    /**
     * @test
     * @expectedException \TypeError
     */
    public function testCollectingInvalidWidgets()
    {
        new WidgetCollection([], (object)[], 123);
    }

    public function validWidgetDataProvider(): array
    {
        return [
            [
                new Widget('Test Widget 1', 'TEST1', Money::USD(1234)),
                new Widget('Test Widget 2', 'TEST2', Money::USD(5678)),
                new Widget('Test Widget 3', 'TEST3', Money::USD(123)),
                new Widget('Test Widget 4', 'TEST4', Money::USD("12345678")),
            ],
        ];
    }

    public function invalidWidgetDataProvider(): array
    {
        return [
            [[]],
            [(object)[]],
            [1, 2, 3],
            [new WidgetCollection],
        ];
    }
}