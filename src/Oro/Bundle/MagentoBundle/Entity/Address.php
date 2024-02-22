<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Represents an address.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
#[ORM\HasLifecycleCallbacks]
#[Config(defaultValues: ['activity' => ['immutable' => true], 'attachment' => ['immutable' => true]])]
#[ORM\Entity]
#[ORM\Table('orocrm_magento_customer_addr')]
class Address extends AbstractTypedAddress implements
    OriginAwareInterface,
    IntegrationAwareInterface,
    ExtendEntityInterface
{
    const SYNC_TO_MAGENTO = 1;
    const MAGENTO_REMOVED = 2;

    use IntegrationEntityTrait, OriginTrait, CountryTextTrait, ExtendEntityTrait;

    /*
     * FIELDS are duplicated to enable dataaudit only for customer address fields
     */
    #[ORM\Column(name: 'label', type: 'string', length: 255, nullable: true)]
    protected ?string $label = null;

    #[ORM\Column(name: 'street', type: 'string', length: 500, nullable: true)]
    protected ?string $street = null;

    #[ORM\Column(name: 'street2', type: 'string', length: 500, nullable: true)]
    protected ?string $street2 = null;

    #[ORM\Column(name: 'city', type: 'string', length: 255, nullable: true)]
    protected ?string $city = null;

    #[ORM\Column(name: 'postal_code', type: 'string', length: 255, nullable: true)]
    protected ?string $postalCode = null;

    /**
     * @var Country
     */
    #[ORM\ManyToOne(targetEntity: 'Oro\Bundle\AddressBundle\Entity\Country')]
    #[ORM\JoinColumn(name: 'country_code', referencedColumnName: 'iso2_code')]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 140]])]
    protected $country;

    /**
     * @var Region
     */
    #[ORM\ManyToOne(targetEntity: 'Oro\Bundle\AddressBundle\Entity\Region')]
    #[ORM\JoinColumn(name: 'region_code', referencedColumnName: 'combined_code')]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 130]])]
    protected $region;

    #[ORM\Column(name: 'region_text', type: 'string', length: 255, nullable: true)]
    protected ?string $regionText = null;

    #[ORM\Column(name: 'name_prefix', type: 'string', length: 255, nullable: true)]
    protected ?string $namePrefix = null;

    #[ORM\Column(name: 'first_name', type: 'string', length: 255, nullable: true)]
    protected ?string $firstName = null;

    #[ORM\Column(name: 'middle_name', type: 'string', length: 255, nullable: true)]
    protected ?string $middleName = null;

    #[ORM\Column(name: 'last_name', type: 'string', length: 255, nullable: true)]
    protected ?string $lastName = null;

    #[ORM\Column(name: 'name_suffix', type: 'string', length: 255, nullable: true)]
    protected ?string $nameSuffix = null;

    #[ORM\ManyToOne(targetEntity: 'Customer', inversedBy: 'addresses')]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ConfigField(defaultValues: ['importexport' => ['full' => false]])]
    protected $owner;

    /**
     * @var string
     */
    #[ORM\Column(name: 'phone', type: 'string', length: 255, nullable: true)]
    protected $phone;

    #[ORM\ManyToMany(targetEntity: 'Oro\Bundle\AddressBundle\Entity\AddressType')]
    #[ORM\JoinTable(name: 'orocrm_magento_cust_addr_type', joinColumns: [new ORM\JoinColumn(name: 'customer_address_id', referencedColumnName: 'id', onDelete: 'CASCADE')], inverseJoinColumns: [new ORM\JoinColumn(name: 'type_name', referencedColumnName: 'name')])]
    protected ?Collection $types = null;

    /**
     * @var ContactAddress
     */
    #[ORM\OneToOne(targetEntity: 'Oro\Bundle\ContactBundle\Entity\ContactAddress')]
    #[ORM\JoinColumn(name: 'related_contact_address_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected $contactAddress;

    /**
     * @var ContactPhone
     */
    #[ORM\OneToOne(targetEntity: 'Oro\Bundle\ContactBundle\Entity\ContactPhone')]
    #[ORM\JoinColumn(name: 'related_contact_phone_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected $contactPhone;

    /**
     * @var int
     */
    #[ORM\Column(name: 'sync_state', type: 'integer', nullable: true)]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected $syncState;

    /**
     * Set contact as owner.
     *
     * @param Customer $owner
     */
    public function setOwner(Customer $owner = null)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner customer.
     *
     * @return Customer
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param ContactAddress $contactAddress
     */
    public function setContactAddress($contactAddress)
    {
        $this->contactAddress = $contactAddress;
    }

    /**
     * @return ContactAddress
     */
    public function getContactAddress()
    {
        return $this->contactAddress;
    }

    /**
     * Set address created date/time
     *
     * @param \DateTime $created
     * @return Address|AbstractAddress
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get address created date/time
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set address updated date/time
     *
     * @param \DateTime $updated
     * @return Address|AbstractAddress
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * Get address last update date/time
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param ContactPhone $contactPhone
     */
    public function setContactPhone($contactPhone)
    {
        $this->contactPhone = $contactPhone;
    }

    /**
     * @return ContactPhone
     */
    public function getContactPhone()
    {
        return $this->contactPhone;
    }

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
     * @return int
     */
    public function getSyncState()
    {
        return $this->syncState;
    }

    /**
     * @param int $syncState
     * @return Address
     */
    public function setSyncState($syncState)
    {
        $this->syncState = $syncState;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }
}
