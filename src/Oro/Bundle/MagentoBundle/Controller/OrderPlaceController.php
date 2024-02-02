<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\ImportExportBundle\Writer\EntityWriter;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Magento Order Place Controller
 */
#[Route(path: '/order/place')]
class OrderPlaceController extends AbstractController
{
    const SYNC_SUCCESS    = 'success';
    const SYNC_ERROR      = 'error';

    /**
     * @param Cart $cart
     * @return array
     */
    #[Route(path: '/cart/{id}', name: 'oro_magento_orderplace_cart', requirements: ['id' => '\d+'])]
    public function cartAction(Cart $cart)
    {
        $urlGenerator = $this
            ->get('oro_magento.service.magento_url_generator')
            ->setChannel($cart->getChannel())
            ->setFlowName('oro_sales_new_order')
            ->setOrigin('quote')
            ->generate(
                $cart->getOriginId(),
                'oro_magento_orderplace_success',
                'oro_magento_orderplace_error'
            );

        $translator = $this->container->get('translator');

        return [
            'error'     => $urlGenerator->isError() ? $translator->trans($urlGenerator->getError()) : false,
            'sourceUrl' => $urlGenerator->getSourceUrl(),
            'cartId'    => $cart->getId(),
        ];
    }

    /**
     * @param Cart $cart
     * @return JsonResponse
     */
    #[Route(path: '/sync/{id}', name: 'oro_magento_orderplace_new_cart_order_sync', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[CsrfProtection]
    public function syncAction(Cart $cart)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        try {
            $isOrderLoaded = $this->loadOrderInformation(
                $cart->getChannel(),
                [
                    'filters' => ['quote_id' => $cart->getOriginId()],
                    ProcessorRegistry::TYPE_IMPORT => [EntityWriter::SKIP_CLEAR => true]
                ]
            );

            $isCartLoaded = $this->loadCartInformation(
                $cart->getChannel(),
                [
                    'filters' => ['entity_id' => $cart->getOriginId()],
                    ProcessorRegistry::TYPE_IMPORT => [EntityWriter::SKIP_CLEAR => true]
                ]
            );

            if (!$isOrderLoaded || !$isCartLoaded) {
                throw new \LogicException('Unable to load information.');
            }

            $order = $em->getRepository(Order::class)->getLastPlacedOrderBy($cart, 'cart');
            if (null === $order) {
                throw new \LogicException('Unable to load order.');
            }

            $redirectUrl = $this->generateUrl('oro_magento_order_view', ['id' => $order->getId()]);
            $message = $this->container->get('translator')->trans('oro.magento.controller.synchronization_success');
            $status = self::SYNC_SUCCESS;
        } catch (\Exception $e) {
            $cart->setStatusMessage('oro.magento.controller.synchronization_failed_status');
            $em->flush($cart);
            $redirectUrl = $this->generateUrl('oro_magento_cart_view', ['id' => $cart->getId()]);
            $message = $this->container->get('translator')->trans('oro.magento.controller.sync_error_with_magento');
            $status = self::SYNC_ERROR;
        }

        return new JsonResponse(
            [
                'statusType' => $status,
                'message' => $message,
                'url' => $redirectUrl
            ]
        );
    }

    /**
     * @param Customer $customer
     * @return array
     */
    #[Route(path: '/customer/{id}', name: 'oro_magento_widget_customer_orderplace', requirements: ['id' => '\d+'])]
    public function customerAction(Customer $customer)
    {
        $urlGenerator = $this
            ->get('oro_magento.service.magento_url_generator')
            ->setChannel($customer->getChannel())
            ->setFlowName('oro_sales_new_order')
            ->setOrigin('customer')
            ->generate(
                $customer->getOriginId(),
                'oro_magento_orderplace_success',
                'oro_magento_orderplace_error'
            );

        $translator = $this->container->get('translator');

        return [
            'error'       => $urlGenerator->isError() ? $translator->trans($urlGenerator->getError()) : false,
            'sourceUrl'   => $urlGenerator->getSourceUrl(),
            'customerId'  => $customer->getId(),
        ];
    }

    /**
     * @param Customer $customer
     * @return JsonResponse
     */
    #[Route(path: '/customer_sync/{id}', name: 'oro_magento_orderplace_new_customer_order_sync', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[CsrfProtection]
    public function customerSyncAction(Customer $customer)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        try {
            $isOrderLoaded = $this->loadOrderInformation(
                $customer->getChannel(),
                ['filters' => ['customer_id' => $customer->getOriginId()]]
            );
            if (!$isOrderLoaded) {
                throw new \LogicException('Unable to load order.');
            }
            $order = $em->getRepository(Order::class)->getLastPlacedOrderBy($customer, 'customer');
            if (null === $order) {
                throw new \LogicException('Unable to load order.');
            }

            $redirectUrl = $this->generateUrl('oro_magento_order_view', ['id' => $order->getId()]);
            $message = $this->container->get('translator')->trans('oro.magento.controller.synchronization_success');
            $status = self::SYNC_SUCCESS;
        } catch (\Exception $e) {
            $redirectUrl = $this->generateUrl('oro_magento_customer_view', ['id' => $customer->getId()]);
            $message = $this->container->get('translator')->trans('oro.magento.controller.sync_error_with_magento');
            $status = self::SYNC_ERROR;
        }
        return new JsonResponse(
            [
                'statusType' => $status,
                'message' => $message,
                'url' => $redirectUrl
            ]
        );
    }

    #[Route(path: '/success', name: 'oro_magento_orderplace_success')]
    #[Template]
    public function successAction()
    {
        return [];
    }

    #[Route(path: '/error', name: 'oro_magento_orderplace_error')]
    public function errorAction()
    {
        return [];
    }

    /**
     * Adds message to flash bag
     *
     * @param string $message
     * @param string $type
     */
    protected function addMessage($message, $type = 'success', Request $request)
    {
        $request->getSession()->getFlashBag()->add($type, $this->container->get('translator')->trans($message));
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

    /**
     * @param Channel $channel
     * @param array $configuration
     * @return bool
     */
    protected function loadCartInformation(Channel $channel, array $configuration = [])
    {
        $cartInformationLoader = $this->container->get('oro_magento.service.cart.information_loader');

        return $cartInformationLoader->load($channel, $configuration);
    }
}
