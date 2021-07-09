<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Reader;

use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\ExecutionContext;
use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Psr\Log\NullLogger;

abstract class AbstractInfoReaderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|ContextRegistry */
    protected $contextRegistry;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ConnectorContextMediator */
    protected $contextMediator;

    /** @var \PHPUnit\Framework\MockObject\MockObject|StepExecution */
    protected $stepExecutionMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ContextInterface */
    protected $context;

    /** @var \PHPUnit\Framework\MockObject\MockObject|MagentoTransportInterface */
    protected $transport;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ExecutionContext */
    protected $jobExecution;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ExecutionContext */
    protected $executionContext;

    /** @var LoggerStrategy */
    protected $logger;

    protected function setUp(): void
    {
        $this->contextRegistry = $this->createMock(ContextRegistry::class);

        $this->logger = new LoggerStrategy(new NullLogger());

        $this->contextMediator = $this
            ->getMockBuilder(ConnectorContextMediator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stepExecutionMock = $this->getMockBuilder(StepExecution::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->createMock(ContextInterface::class);

        $this->contextRegistry->expects($this->any())
            ->method('getByStepExecution')
            ->will($this->returnValue($this->context));

        $channel = $this->createMock(Channel::class);
        $transportSettings = $this->getMockForAbstractClass(Transport::class);

        $channel->expects($this->any())
            ->method('getTransport')
            ->will($this->returnValue($transportSettings));

        $this->transport = $this->createMock(MagentoTransportInterface::class);
        $this->contextMediator->expects($this->any())
            ->method('getInitializedTransport')
            ->will($this->returnValue($this->transport));

        $this->contextMediator->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($channel));

        $this->executionContext = $this->createMock(ExecutionContext::class);
        $this->jobExecution = $this->createMock(JobExecution::class);
        $this->jobExecution->expects($this->any())
            ->method('getExecutionContext')
            ->will($this->returnValue($this->executionContext));

        $this->stepExecutionMock->expects($this->once())
            ->method('getJobExecution')
            ->will($this->returnValue($this->jobExecution));
    }

    /**
     * @return ItemReaderInterface|StepExecutionAwareInterface
     */
    abstract protected function getReader();
}
