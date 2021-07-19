<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Writer;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Tests\Unit\ImportExport\Writer\PersistentBatchWriterTest;
use Oro\Bundle\MagentoBundle\ImportExport\Writer\AbstractExportWriter;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

abstract class AbstractExportWriterTest extends PersistentBatchWriterTest
{
    private ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject $registry;

    private ContextRegistry|\PHPUnit\Framework\MockObject\MockObject $contextRegistry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->contextRegistry = $this->createMock(ContextRegistry::class);
    }

    protected AbstractExportWriter $writer;

    public function testChannelIdMissing()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Channel id is missing');

        $transport = $this->createMock(MagentoTransportInterface::class);
        $this->writer->setTransport($transport);

        $stepExecution = $this->getMockBuilder(StepExecution::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->createMock(ContextInterface::class);
        $this->contextRegistry->expects($this->once())
            ->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $context->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('channel'))
            ->will($this->returnValue(false));

        $this->writer->setStepExecution($stepExecution);

        $this->writer->write([['customer_id' => 1]]);
    }

    public function testChannelMissing()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Channel is missing');

        $transport = $this->createMock(MagentoTransportInterface::class);
        $this->writer->setTransport($transport);

        $stepExecution = $this->getMockBuilder(StepExecution::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->any())->method('find')
            ->will($this->returnValue(null));

        $this->registry->expects($this->any())->method('getRepository')
            ->will($this->returnValue($repository));

        $context = $this->createMock(ContextInterface::class);
        $context->expects($this->once())
            ->method('getOption')
            ->with($this->equalTo('channel'))
            ->will($this->returnValue(1));
        $context->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('channel'))
            ->will($this->returnValue(true));

        $this->contextRegistry->expects($this->atLeastOnce())
            ->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $this->writer->setStepExecution($stepExecution);

        $this->writer->write([['customer_id' => 1]]);
    }
}
