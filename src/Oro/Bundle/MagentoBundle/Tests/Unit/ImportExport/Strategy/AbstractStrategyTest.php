<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;

use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\ChainEntityClassNameProvider;
use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;
use Oro\Bundle\ImportExportBundle\Field\RelatedEntityStateHelper;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Strategy\Import\NewEntitiesHelper;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use Oro\Bundle\IntegrationBundle\ImportExport\Helper\DefaultOwnerHelper;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\AbstractImportStrategy;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
abstract class AbstractStrategyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ImportStrategyHelper
     */
    protected $strategyHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|StepExecution
     */
    protected $stepExecution;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|JobExecution
     */
    protected $jobExecution;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FieldHelper
     */
    protected $fieldHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DatabaseHelper
     */
    protected $databaseHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DefaultOwnerHelper
     */
    protected $defaultOwnerHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ChannelHelper
     */
    protected $channelHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|AddressImportHelper
     */
    protected $addressHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ChainEntityClassNameProvider
     */
    protected $chainEntityClassNameProvider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|TranslatorInterface
     */
    protected $translator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var NewEntitiesHelper
     */
    protected $newEntitiesHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject|RelatedEntityStateHelper */
    protected $relatedEntityStateHelper;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->fieldHelper = $this->getMockBuilder(FieldHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldHelper->expects($this->any())
            ->method('getIdentityValues')
            ->willReturn([]);

        $this->fieldHelper->expects($this->any())
            ->method('getFields')
            ->willReturn([]);

        $this->databaseHelper = $this->getMockBuilder(DatabaseHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->strategyHelper = $this
            ->getMockBuilder(ImportStrategyHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->strategyHelper->expects($this->any())
            ->method('checkPermissionGrantedForEntity')
            ->will($this->returnValue(true));

        $this->defaultOwnerHelper = $this
            ->getMockBuilder(DefaultOwnerHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->channelHelper = $this
            ->getMockBuilder(ChannelHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressHelper = $this
            ->getMockBuilder(AddressImportHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stepExecution = $this->getMockBuilder(StepExecution::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jobExecution = $this->getMockBuilder(JobExecution::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stepExecution->expects($this->any())->method('getJobExecution')
            ->will($this->returnValue($this->jobExecution));

        $this->chainEntityClassNameProvider = $this
            ->getMockBuilder(ChainEntityClassNameProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->relatedEntityStateHelper = $this->createMock(RelatedEntityStateHelper::class);

        $this->newEntitiesHelper = new NewEntitiesHelper();
        $this->logger = new NullLogger();
    }

    protected function tearDown(): void
    {
        unset(
            $this->eventDispatcher,
            $this->strategyHelper,
            $this->fieldHelper,
            $this->databaseHelper,
            $this->strategy,
            $this->stepExecution,
            $this->jobExecution,
            $this->defaultOwnerHelper,
            $this->logger,
            $this->channelHelper,
            $this->addressHelper,
            $this->doctrineHelper,
            $this->newEntitiesHelper
        );
    }

    /**
     * @return StrategyInterface|AbstractImportStrategy
     */
    abstract protected function getStrategy();
}
