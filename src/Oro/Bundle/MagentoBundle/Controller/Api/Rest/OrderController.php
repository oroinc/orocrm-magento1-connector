<?php

namespace Oro\Bundle\MagentoBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API CRUD controller for Order entity.
 */
#[RouteResource('order')]
#[NamePrefix(['value' => 'oro_api_'])]
class OrderController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->container->get('oro_magento.order.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('oro_magento.form.order.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_magento.form.handler.order.api');
    }

    /**
     * Get all orders.
     *
     * @ApiDoc(
     *      description="Get all orders",
     *      resource=true
     * )
     * @param Request $request
     * @return Response
     */
    #[AclAncestor('oro_magento_order_view')]
    #[QueryParam(name: 'page', requirements: '\d+', description: 'Page number, starting from 1. Defaults to 1.', nullable: true)]
    #[QueryParam(name: 'limit', requirements: '\d+', description: 'Number of items per page. defaults to 10.', nullable: true)]
    public function cgetAction(Request $request)
    {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * Create new order.
     *
     * @ApiDoc(
     *      description="Create new order",
     *      resource=true
     * )
     */
    #[Acl(id: 'oro_magento_order_create', type: 'entity', class: 'Oro\Bundle\MagentoBundle\Entity\Order', permission: 'CREATE')]
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Get order.
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Get order",
     *      resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_magento_order_view')]
    #[Get(requirements: ['id' => '\d+'])]
    public function getAction(int $id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * Update order.
     *
     * @param int $id Order id
     *
     * @ApiDoc(
     *      description="Update order",
     *      resource=true
     * )
     * @return Response
     */
    #[Acl(id: 'oro_magento_order_update', type: 'entity', class: 'Oro\Bundle\MagentoBundle\Entity\Order', permission: 'EDIT')]
    #[Put(requirements: ['id' => '\d+'])]
    public function putAction(int $id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Delete order.
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete order",
     *      resource=true
     * )
     * @return Response
     */
    #[Acl(id: 'oro_magento_order_delete', type: 'entity', class: 'Oro\Bundle\MagentoBundle\Entity\Order', permission: 'DELETE')]
    #[Delete(requirements: ['id' => '\d+'])]
    public function deleteAction(int $id)
    {
        return $this->handleDeleteRequest($id);
    }
}
