<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Oro\Bundle\ImportExportBundle\Job\JobExecutor;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use Oro\Bundle\MagentoBundle\Model\NewsletterSubscriberManager;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Magento Newsletter Subscriber Controller
 */
#[Route(path: '/newsletter-subscriber')]
class NewsletterSubscriberController extends AbstractController
{
    #[Route(path: '/', name: 'oro_magento_newsletter_subscriber_index')]
    #[AclAncestor('oro_magento_newsletter_subscriber_view')]
    #[Template]
    public function indexAction()
    {
        return [
            'entity_class' => NewsletterSubscriber::class
        ];
    }

    /**
     * @param NewsletterSubscriber $newsletterSubscriber
     * @return array
     */
    #[Route(path: '/view/{id}', name: 'oro_magento_newsletter_subscriber_view', requirements: ['id' => '\d+'])]
    public function viewAction(NewsletterSubscriber $newsletterSubscriber)
    {
        return ['entity' => $newsletterSubscriber];
    }

    /**
     * @param NewsletterSubscriber $newsletterSubscriber
     * @param Request $request
     * @return array
     */
    #[Route(path: '/info/{id}', name: 'oro_magento_newsletter_subscriber_info', requirements: ['id' => '\d+'])]
    public function infoAction(Request $request, NewsletterSubscriber $newsletterSubscriber)
    {
        return ['entity' => $newsletterSubscriber, 'useCustomer' => $request->get('useCustomer')];
    }

    /**
     * @param NewsletterSubscriber $newsletterSubscriber
     * @return JsonResponse
     */
    #[Route(path: '/subscribe/{id}', name: 'oro_magento_newsletter_subscriber_subscribe', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[CsrfProtection]
    #[Acl(id: 'oro_magento_newsletter_subscriber_subscribe', type: 'entity', permission: 'EDIT', class: 'Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber')]
    public function subscribeAction(NewsletterSubscriber $newsletterSubscriber)
    {
        return new JsonResponse($this->doJob($newsletterSubscriber, NewsletterSubscriber::STATUS_SUBSCRIBED));
    }

    /**
     * @param NewsletterSubscriber $newsletterSubscriber
     * @return JsonResponse
     */
    #[Route(path: '/unsubscribe/{id}', name: 'oro_magento_newsletter_subscriber_unsubscribe', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[CsrfProtection]
    #[Acl(id: 'oro_magento_newsletter_subscriber_unsubscribe', type: 'entity', permission: 'EDIT', class: 'Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber')]
    public function unsubscribeAction(NewsletterSubscriber $newsletterSubscriber)
    {
        return new JsonResponse($this->doJob($newsletterSubscriber, NewsletterSubscriber::STATUS_UNSUBSCRIBED));
    }

    /**
     * @param Customer $customer
     * @return JsonResponse
     */
    #[Route(path: '/subscribe/customer/{id}', name: 'oro_magento_newsletter_subscriber_subscribe_customer', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[CsrfProtection]
    #[Acl(id: 'oro_magento_newsletter_subscriber_subscribe_customer', type: 'entity', permission: 'EDIT', class: 'Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber')]
    public function subscribeByCustomerAction(Customer $customer)
    {
        return $this->processCustomerSubscription($customer, NewsletterSubscriber::STATUS_SUBSCRIBED);
    }

    /**
     * @param Customer $customer
     * @return JsonResponse
     */
    #[Route(path: '/unsubscribe/customer/{id}', name: 'oro_magento_newsletter_subscriber_unsubscribe_customer', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[CsrfProtection]
    #[Acl(id: 'oro_magento_newsletter_subscriber_unsubscribe_customer', type: 'entity', permission: 'EDIT', class: 'Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber')]
    public function unsubscribeByCustomerAction(Customer $customer)
    {
        return $this->processCustomerSubscription($customer, NewsletterSubscriber::STATUS_UNSUBSCRIBED);
    }

    /**
     * @param NewsletterSubscriber $newsletterSubscriber
     * @param int $statusIdentifier
     *
     * @return array
     */
    protected function doJob(NewsletterSubscriber $newsletterSubscriber, $statusIdentifier)
    {
        $jobResult = $this->container->get(JobExecutor::class)->executeJob(
            'export',
            'magento_newsletter_subscriber_export',
            [
                'channel' => $newsletterSubscriber->getChannel()->getId(),
                'entity' => $newsletterSubscriber,
                'statusIdentifier' => $statusIdentifier,
                'writer_skip_clear' => true,
                'processorAlias' => 'oro_magento'
            ]
        );

        return [
            'successful' => $jobResult->isSuccessful(),
            'error' => $jobResult->getFailureExceptions()
        ];
    }

    /**
     * @param Customer $customer
     * @param int $status
     *
     * @return JsonResponse
     */
    protected function processCustomerSubscription(Customer $customer, $status)
    {
        $newsletterSubscribers = $this->container->get(NewsletterSubscriberManager::class)
            ->getOrCreateFromCustomer($customer);

        $jobResult = ['successful' => false];
        foreach ($newsletterSubscribers as $newsletterSubscriber) {
            if ($newsletterSubscriber->getStatus()->getId() != $status) {
                $jobResult = $this->doJob($newsletterSubscriber, $status);
                if (!$jobResult['successful']) {
                    break;
                }
            }
        }

        return new JsonResponse($jobResult);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            JobExecutor::class,
            NewsletterSubscriberManager::class,
        ];
    }
}
