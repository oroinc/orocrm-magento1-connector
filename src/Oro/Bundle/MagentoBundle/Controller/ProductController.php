<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Oro\Bundle\MagentoBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/product')]
class ProductController extends AbstractController
{
    #[Route(path: '/', name: 'oro_magento_product_index')]
    #[AclAncestor('oro_magento_product_view')]
    #[Template]
    public function indexAction()
    {
        return [];
    }

    #[Route(path: '/view/{id}', name: 'oro_magento_product_view', requirements: ['id' => '\d+'])]
    public function viewAction(Product $customer)
    {
        return ['entity' => $customer];
    }

    #[Route(path: '/info/{id}', name: 'oro_magento_product_info', requirements: ['id' => '\d+'])]
    public function infoAction(Product $customer)
    {
        return ['entity' => $customer];
    }
}
