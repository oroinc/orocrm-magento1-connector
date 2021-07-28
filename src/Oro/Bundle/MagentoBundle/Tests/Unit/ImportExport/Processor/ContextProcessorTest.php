<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Processor;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Serializer\SerializerInterface;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use Oro\Bundle\MagentoBundle\ImportExport\Processor\ContextProcessor;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ContextProcessorTest extends \PHPUnit\Framework\TestCase
{
    private ContextProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new ContextProcessor();
    }

    public function testProcess(): void
    {
        $item = ['property' => 'value'];
        $expectedProperty = 'property2';
        $expectedValue = 'value2';

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects(self::once())
            ->method('denormalize')
            ->willReturnCallback(fn ($item) => (object)$item);

        $this->processor->setSerializer($serializer);

        $strategy = $this->createMock(StrategyInterface::class);
        $strategy->expects(self::once())
            ->method('process')
            ->with(self::isType('object'))
            ->willReturnCallback(
                function ($item) use ($expectedProperty, $expectedValue) {
                    $item->{$expectedProperty} = $expectedValue;

                    return $item;
                }
            );

        $this->processor->setStrategy($strategy);

        $this->processor->setEntityName(\stdClass::class);

        $context = $this->createMock(ContextInterface::class);
        $context->expects(self::once())->method('getConfiguration')->willReturn([]);

        $this->processor->setImportExportContext($context);

        $result = $this->processor->process($item);

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        self::assertNotEmpty(
            $propertyAccessor->getValue($result, $expectedProperty),
            $expectedValue
        );
    }
}
