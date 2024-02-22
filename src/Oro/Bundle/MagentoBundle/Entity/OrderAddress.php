<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Refers to the address information that was used by customer users
 * as the billing or shipping address in their orders.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
#[Config(defaultValues: ['entity' => ['icon' => 'fa-map-marker'], 'activity' => ['immutable' => true], 'attachment' => ['immutable' => true]])]
#[ORM\Table('orocrm_magento_order_address')]
class OrderAddress extends AbstractTypedAddress implements
    IntegrationAwareInterface,
    OriginAwareInterface,
    ExtendEntityInterface
{
    use IntegrationEntityTrait, OriginTrait, CountryTextTrait, ExtendEntityTrait;

    #[ORM\ManyToMany(targetEntity: 'Oro\Bundle\AddressBundle\Entity\AddressType')]
    #[ORM\JoinTable(name: 'orocrm_magento_order_addr_type', joinColumns: [new ORM\JoinColumn(name: 'order_address_id', referencedColumnName: 'id', onDelete: 'CASCADE')], inverseJoinColumns: [new ORM\JoinColumn(name: 'type_name', referencedColumnName: 'name')])]
    protected ?Collection $types = null;

    /**
     * @var Order
     */
    #[ORM\ManyToOne(targetEntity: 'Order', inversedBy: 'addresses', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $owner;

    /**
     * @var string
     */
    #[ORM\Column(name: 'fax', type: 'string', length: 255, nullable: true)]
    protected $fax;

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
     * Unset no used fields from mapping
     * Name parts unused due to magento api does not bring it up
     */
    protected ?string $label = null;
    protected ?string $namePrefix = null;
    protected ?string $middleName = null;
    protected ?string $nameSuffix = null;
    protected ?string $street2 = null;
    protected ?bool $primary = null;
    protected ?\DateTimeInterface $created = null;
    protected ?\DateTimeInterface $updated = null;

    /**
     * @param Order $owner
     *
     * @return OrderAddress
     */
    public function setOwner(Order $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Order
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param string $fax
     *
     * @return OrderAddress
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param string $phone
     *
     * @return OrderAddress
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
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
