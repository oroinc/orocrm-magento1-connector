<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Stub;

use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

interface StepExecutionAwareWriter extends StepExecutionAwareInterface, ItemWriterInterface
{
}
