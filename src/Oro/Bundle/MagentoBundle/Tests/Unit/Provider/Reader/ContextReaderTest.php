<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Reader;

use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\ExecutionContext;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\MagentoBundle\Provider\Reader\ContextReader;

class ContextReaderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|ContextRegistry */
    protected $contextRegistry;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ExecutionContext */
    protected $executionContext;

    /** @var \PHPUnit\Framework\MockObject\MockObject|JobExecution */
    protected $jobExecution;

    protected function setUp(): void
    {
        $this->contextRegistry = $this->getMockBuilder(ContextRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testReadEmpty()
    {
        $this->assertEmpty($this->getReader()->read());
    }

    public function testInitializeAndRead()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|StepExecution $stepExecution */
        $stepExecution = $this->getMockBuilder(StepExecution::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->createMock(ContextInterface::class);

        $this->contextRegistry->expects($this->once())
            ->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $this->executionContext = $this->createMock(ExecutionContext::class);

        $this->jobExecution = $this->createMock(JobExecution::class);
        $this->jobExecution->expects($this->any())
            ->method('getExecutionContext')
            ->will($this->returnValue($this->executionContext));

        $data = $this->getData();
        $this->executionContext->expects($this->once())
            ->method('get')
            ->will($this->returnValue($data));

        $stepExecution->expects($this->once())
            ->method('getJobExecution')
            ->will($this->returnValue($this->jobExecution));

        $reader = $this->getReader();

        $reader->setStepExecution($stepExecution);

        foreach ($data as $item) {
            $this->assertEquals($item, $reader->read());
        }

        $this->assertNull($reader->read());
    }

    /**
     * @param string $contextKey
     *
     * @return ContextReader
     */
    protected function getReader($contextKey = 'ids')
    {
        $reader = new ContextReader($this->contextRegistry);
        $reader->setContextKey($contextKey);

        return $reader;
    }

    /**
     * @return array
     */
    protected function getData()
    {
        $obj = new \stdClass();
        $obj->prop = 1;

        $obj2 = new \stdClass();
        $obj2->prop = 2;

        return [$obj, $obj2];
    }

    public function testReadFailed()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Context key is missing');

        $reader = $this->getReader(null);

        /** @var \PHPUnit\Framework\MockObject\MockObject|StepExecution $stepExecution */
        $stepExecution = $this->getMockBuilder(StepExecution::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->createMock(ContextInterface::class);

        $this->contextRegistry->expects($this->once())
            ->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $reader->setStepExecution($stepExecution);
    }
}
