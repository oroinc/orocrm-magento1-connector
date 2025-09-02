<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The controller for Magento Order entity.
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
#[Route(path: '/order')]
class OrderController extends AbstractController
{
    #[Route(path: '/', name: 'oro_magento_order_index')]
    #[AclAncestor('oro_magento_order_view')]
    #[\Symfony\Bridge\Twig\Attribute\Template(template: '@OroMagento/Order/index.html.twig')]
    public function indexAction()
    {
        return [
            'entity_class' => Order::class
        ];
    }

    /**
     * @param Order $order
     * @return array
     */
    #[Route(path: '/view/{id}', name: 'oro_magento_order_view', requirements: ['id' => '\d+'])]
    public function viewAction(Order $order)
    {
        return ['entity' => $order];
    }

    /**
     * @param Order $order
     * @return array
     */
    #[Route(path: '/info/{id}', name: 'oro_magento_order_widget_info', requirements: ['id' => '\d+'])]
    public function infoAction(Order $order)
    {
        return ['entity' => $order];
    }

    /**
     * @param Order $order
     * @return array
     */
    #[Route(path: '/widget/grid/{id}', name: 'oro_magento_order_widget_items', requirements: ['id' => '\d+'])]
    public function itemsAction(Order $order)
    {
        return ['entity' => $order];
    }

    /**
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    #[Route(path: '/account-widget/customer-orders/{customerId}/{channelId}', name: 'oro_magento_widget_customer_orders', requirements: ['customerId' => '\d+', 'channelId' => '\d+'])]
    #[AclAncestor('oro_magento_order_view')]
    #[\Symfony\Bridge\Twig\Attribute\Template(template: '@OroMagento/Order/customerOrders.html.twig')]
    public function customerOrdersAction(#[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'customerId')]
    Customer $customer, #[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'channelId')]
    Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    #[Route(path: '/account-widget/customer-recent-purchases/{customerId}/{channelId}', name: 'oro_magento_widget_customer_recent_purchases', requirements: ['customerId' => '\d+', 'channelId' => '\d+'])]
    #[AclAncestor('oro_magento_order_view')]
    #[\Symfony\Bridge\Twig\Attribute\Template(template: '@OroMagento/Order/customerRecentPurchases.html.twig')]
    public function customerRecentPurchasesAction(#[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'customerId')]
    Customer $customer, #[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'channelId')]
    Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    #[Route(path: '/customer-widget/customer-orders/{customerId}/{channelId}', name: 'oro_magento_customer_orders_widget', requirements: ['customerId' => '\d+', 'channelId' => '\d+'])]
    #[\Symfony\Bridge\Twig\Attribute\Template(template: '@OroMagento/Order/customerOrdersWidget.html.twig')]
    public function customerOrdersWidgetAction(#[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'customerId')]
    Customer $customer, #[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'channelId')]
    Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    #[Route(path: '/customer-widget/customer-recent-purchases/{customerId}/{channelId}', name: 'oro_magento_customer_recent_purchases_widget', requirements: ['customerId' => '\d+', 'channelId' => '\d+'])]
    #[\Symfony\Bridge\Twig\Attribute\Template(template: '@OroMagento/Order/customerRecentPurchasesWidget.html.twig')]
    public function customerRecentPurchasesWidgetAction(#[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'customerId')]
    Customer $customer, #[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'channelId')]
    Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    #[Route(path: '/account-widget/order-notes/{customerId}/{channelId}', name: 'oro_magento_widget_customer_order_notes', requirements: ['customerId' => '\d+', 'channelId' => '\d+'])]
    #[AclAncestor('oro_magento_order_view')]
    #[\Symfony\Bridge\Twig\Attribute\Template(template: '@OroMagento/Order/customerOrderNotes.html.twig')]
    public function customerOrderNotesAction(#[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'customerId')]
    Customer $customer, #[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'channelId')]
    Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @param Customer $customer
     * @param Channel $channel
     * @return array
     */
    #[Route(path: '/customer-widget/order-notes/{customerId}/{channelId}', name: 'oro_magento_customer_order_notes_widget', requirements: ['customerId' => '\d+', 'channelId' => '\d+'])]
    #[AclAncestor('oro_magento_order_view')]
    #[\Symfony\Bridge\Twig\Attribute\Template(template: '@OroMagento/Order/customerOrderNotesWidget.html.twig')]
    public function customerOrderNotesWidgetAction(#[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'customerId')]
    Customer $customer, #[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'channelId')]
    Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    /**
     * @param Order $order
     * @return array
     */
    #[Route(path: '/widget/order_notes/{orderId}', name: 'oro_magento_order_notes_widget', requirements: ['orderId' => '\d+'])]
    #[AclAncestor('oro_magento_order_view')]
    #[\Symfony\Bridge\Twig\Attribute\Template(template: '@OroMagento/Order/orderNotesWidget.html.twig')]
    public function orderNotesWidgetAction(#[\Symfony\Bridge\Doctrine\Attribute\MapEntity(id: 'orderId')]
    $order)
    {
        return ['order' => $order];
    }

    /**
     * @param Order $order
     * @return RedirectResponse
     */
    #[Route(path: '/actualize/{id}', name: 'oro_magento_order_actualize', requirements: ['id' => '\d+'])]
    public function actualizeAction(Order $order, Request $request)
    {
        $result = false;

        try {
            $result = $this->loadOrderInformation(
                $order->getChannel(),
                [
                    ProcessorRegistry::TYPE_IMPORT => [
                        'filters' => [
                            'increment_id' => $order->getIncrementId()
                        ],
                        'complex_filters' => [
                            'updated_at-gt' => null,
                            'updated_at-lte' => null
                        ]
                    ]
                ]
            );
        } catch (\LogicException $e) {
            $this->container->get('logger')->addCritical($e->getMessage(), ['exception' => $e]);
        }

        if ($result === true) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get('translator')->trans('oro.magento.controller.synchronization_success')
            );
        } else {
            $request->getSession()->getFlashBag()->add(
                'error',
                $this->container->get('translator')->trans('oro.magento.controller.synchronization_error')
            );
        }

        return $this->redirect($this->generateUrl('oro_magento_order_view', ['id' => $order->getId()]));
    }

    /**
     * @param Channel $channel
     * @param array $configuration
     * @return bool
     */
    protected function loadOrderInformation(Channel $channel, array $configuration = [])
    {
        $orderInformationLoader = $this->container->get('oro_magento.service.order.information_loader');

        return $orderInformationLoader->load($channel, $configuration);
    }
}
