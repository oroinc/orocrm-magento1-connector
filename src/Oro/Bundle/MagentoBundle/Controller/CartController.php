<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessor;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\Connector\CartConnector;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The controller for Magento Cart entity.
 */
#[Route(path: '/cart')]
class CartController extends AbstractController
{
    #[Route(path: '/', name: 'oro_magento_cart_index')]
    #[AclAncestor('oro_magento_cart_view')]
    #[Template]
    public function indexAction()
    {
        return [
            'entity_class' => Cart::class,
        ];
    }

    #[Route(path: '/view/{id}', name: 'oro_magento_cart_view', requirements: ['id' => '\d+'])]
    public function viewAction(Cart $cart)
    {
        return ['entity' => $cart];
    }

    #[Route(path: '/info/{id}', name: 'oro_magento_cart_widget_info', requirements: ['id' => '\d+'])]
    public function infoAction(Cart $cart)
    {
        return ['entity' => $cart];
    }

    #[Route(path: '/widget/grid/{id}/{isRemoved}', name: 'oro_magento_cart_widget_items', requirements: ['id' => '\d+', 'isRemoved' => '\d+'])]
    #[AclAncestor('oro_magento_cart_view')]
    #[ParamConverter('cart', class: 'Oro\Bundle\MagentoBundle\Entity\Cart', options: ['id' => 'id'])]
    #[Template]
    public function itemsAction(Cart $cart, $isRemoved = false)
    {
        return ['entity' => $cart, 'is_removed' => (bool)$isRemoved];
    }

    #[Route(path: '/widget/account_cart/{customerId}/{channelId}', name: 'oro_magento_widget_customer_carts', requirements: ['customerId' => '\d+', 'channelId' => '\d+'])]
    #[AclAncestor('oro_magento_cart_view')]
    #[ParamConverter('customer', class: 'Oro\Bundle\MagentoBundle\Entity\Customer', options: ['id' => 'customerId'])]
    #[ParamConverter('channel', class: 'Oro\Bundle\IntegrationBundle\Entity\Channel', options: ['id' => 'channelId'])]
    #[Template]
    public function customerCartsAction(Customer $customer, Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    #[Route(path: '/widget/customer_cart/{customerId}/{channelId}', name: 'oro_magento_customer_carts_widget', requirements: ['customerId' => '\d+', 'channelId' => '\d+'])]
    #[AclAncestor('oro_magento_cart_view')]
    #[ParamConverter('customer', class: 'Oro\Bundle\MagentoBundle\Entity\Customer', options: ['id' => 'customerId'])]
    #[ParamConverter('channel', class: 'Oro\Bundle\IntegrationBundle\Entity\Channel', options: ['id' => 'channelId'])]
    #[Template]
    public function customerCartsWidgetAction(Customer $customer, Channel $channel)
    {
        return ['customer' => $customer, 'channel' => $channel];
    }

    #[Route(path: '/actualize/{id}', name: 'oro_magento_cart_actualize', requirements: ['id' => '\d+'])]
    public function actualizeAction(Cart $cart, Request $request)
    {
        $result = false;
        $connector = $this->container->get(CartConnector::class);

        try {
            $processor = $this->container->get(SyncProcessor::class);
            $result = $processor->process(
                $cart->getChannel(),
                $connector->getType(),
                ['filters' => ['entity_id' => $cart->getOriginId()]]
            );
        } catch (\LogicException $e) {
            $this->container->get(LoggerInterface::class)->addCritical($e->getMessage(), ['exception' => $e]);
        }

        if ($result === true) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('oro.magento.controller.synchronization_success')
            );
        } else {
            $request->getSession()->getFlashBag()->add(
                'error',
                $this->container->get(TranslatorInterface::class)->trans('oro.magento.controller.synchronization_error')
            );
        }

        return $this->redirect($this->generateUrl('oro_magento_cart_view', ['id' => $cart->getId()]));
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                LoggerInterface::class,
                CartConnector::class,
                SyncProcessor::class,
            ]
        );
    }
}
