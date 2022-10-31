<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Async\Topic;

use Oro\Bundle\MagentoBundle\Async\Topic\SyncInitialIntegrationTopic;
use Oro\Bundle\MagentoBundle\Provider\Connector\InitialNewsletterSubscriberConnector;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Test\AbstractTopicTestCase;
use Oro\Component\MessageQueue\Topic\TopicInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class SyncInitialIntegrationTopicTest extends AbstractTopicTestCase
{
    protected function getTopic(): TopicInterface
    {
        return new SyncInitialIntegrationTopic();
    }

    public function validBodyDataProvider(): array
    {
        $requiredOptionsSet = [
            'integration_id' => 1,
        ];
        $fullOptionsSet = array_merge(
            $requiredOptionsSet,
            [
                'connector' => InitialNewsletterSubscriberConnector::TYPE,
                'connector_parameters' => [
                    'skip-dictionary' => true
                ],
            ]
        );

        return [
            'only required options' => [
                'body' => $requiredOptionsSet,
                'expectedBody' => array_merge(
                    $requiredOptionsSet,
                    [
                        'connector' => null,
                        'connector_parameters' => [],
                    ]
                ),
            ],
            'full set of options' => [
                'body' => $fullOptionsSet,
                'expectedBody' => $fullOptionsSet,
            ],
        ];
    }

    public function invalidBodyDataProvider(): array
    {
        return [
            'empty' => [
                'body' => [],
                'exceptionClass' => MissingOptionsException::class,
                'exceptionMessage' =>
                    '/The required option "integration_id" is missing./',
            ],
            'wrong integration_id type' => [
                'body' => [
                    'integration_id' => '1',
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "integration_id" with value "1" is expected to be of type "int"/',
            ],
            'wrong connector type' => [
                'body' => [
                    'integration_id' => 1,
                    'connector' => 1,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "connector" with value 1 is expected to be'
                    . ' of type "string" or "null"/',
            ],
            'wrong connector_parameters type' => [
                'body' => [
                    'integration_id' => 1,
                    'connector_parameters' => 1,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "connector_parameters" with value 1 is expected to be'
                    . ' of type "array"/',
            ],
        ];
    }

    public function testDefaultPriority(): void
    {
        self::assertEquals(MessagePriority::VERY_LOW, $this->getTopic()->getDefaultPriority('queueName'));
    }
}
