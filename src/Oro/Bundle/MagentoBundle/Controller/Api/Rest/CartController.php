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
 * API CRUD controller for Cart entity.
 */
#[RouteResource('cart')]
#[NamePrefix(['value' => 'oro_api_'])]
class CartController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->container->get('oro_magento.cart.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('oro_magento.form.cart.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_magento.form.handler.cart.api');
    }

    /**
     * Get all carts.
     *
     * @QueryParam(
     *     name="page", requirements="\d+", nullable=true, description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *     name="limit", requirements="\d+", nullable=true, description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get all carts",
     *      resource=true
     * )
     * @param Request $request
     * @return Response
     */
    #[AclAncestor('oro_magento_cart_view')]
    public function cgetAction(Request $request)
    {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * Create new cart.
     *
     * @ApiDoc(
     *      description="Create new cart",
     *      resource=true
     * )
     */
    #[Acl(id: 'oro_magento_cart_create', type: 'entity', permission: 'CREATE', class: 'Oro\Bundle\MagentoBundle\Entity\Cart')]
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Get cart.
     *
     * @param int $id
     *
     * @Get(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Get cart",
     *      resource=true
     * )
     *
     * @return Response
     */
    #[AclAncestor('oro_magento_cart_view')]
    public function getAction(int $id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * Update cart.
     *
     * @param int $id Cart id
     *
     * @Put(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Update cart",
     *      resource=true
     * )
     * @return Response
     */
    #[Acl(id: 'oro_magento_cart_update', type: 'entity', permission: 'EDIT', class: 'Oro\Bundle\MagentoBundle\Entity\Cart')]
    public function putAction(int $id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Delete cart.
     *
     * @param int $id
     *
     * @Delete(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Delete cart",
     *      resource=true
     * )
     * @return Response
     */
    #[Acl(id: 'oro_magento_cart_delete', type: 'entity', permission: 'DELETE', class: 'Oro\Bundle\MagentoBundle\Entity\Cart')]
    public function deleteAction(int $id)
    {
        return $this->handleDeleteRequest($id);
    }
}
