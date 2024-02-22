<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

#[ORM\HasLifecycleCallbacks]
#[Config(defaultValues: ['entity' => ['icon' => 'fa-map-marker'], 'activity' => ['immutable' => true], 'attachment' => ['immutable' => true]])]
#[ORM\Entity]
#[ORM\Table('orocrm_magento_cart_address')]
class CartAddress extends AbstractAddress implements OriginAwareInterface, ExtendEntityInterface
{
    use IntegrationEntityTrait, OriginTrait, CountryTextTrait, ExtendEntityTrait;

    /**
     * @var string
     */
    #[ORM\Column(name: 'phone', type: 'string', length: 255, nullable: true)]
    protected $phone;

    #[ORM\Column(name: 'street', type: 'string', length: 500, nullable: true)]
    protected ?string $street = null;

    #[ORM\Column(name: 'city', type: 'string', length: 255, nullable: true)]
    protected ?string $city = null;

    #[ORM\Column(name: 'postal_code', type: 'string', length: 255, nullable: true)]
    protected ?string $postalCode = null;

    /**
     * @var Country
     */
    #[ORM\ManyToOne(targetEntity: 'Oro\Bundle\AddressBundle\Entity\Country')]
    #[ORM\JoinColumn(name: 'country_code', referencedColumnName: 'iso2_code')]
    protected $country;

    /**
     * @var Region
     */
    #[ORM\ManyToOne(targetEntity: 'Oro\Bundle\AddressBundle\Entity\Region')]
    #[ORM\JoinColumn(name: 'region_code', referencedColumnName: 'combined_code')]
    protected $region;

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }
}
