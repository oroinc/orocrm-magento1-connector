<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\BusinessEntitiesBundle\Entity\BaseOrder;
use Oro\Bundle\BusinessEntitiesBundle\Entity\BasePerson;
use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Class Order
 *
 * @package Oro\Bundle\OroMagentoBundle\Entity
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
#[ORM\Entity(repositoryClass: 'Oro\Bundle\MagentoBundle\Entity\Repository\OrderRepository')]
#[ORM\HasLifecycleCallbacks]
#[Config(routeView: 'oro_magento_order_view', defaultValues: ['entity' => ['icon' => 'fa-list-alt'], 'ownership' => ['owner_type' => 'USER', 'owner_field_name' => 'owner', 'owner_column_name' => 'user_owner_id', 'organization_field_name' => 'organization', 'organization_column_name' => 'organization_id'], 'security' => ['type' => 'ACL', 'group_name' => '', 'category' => 'sales_data'], 'grid' => ['default' => 'magento-order-grid', 'context' => 'magento-order-for-context-grid'], 'tag' => ['enabled' => true]])]
#[ORM\Table(name: 'orocrm_magento_order')]
#[ORM\Index(name: 'mageorder_created_idx', columns: ['created_at', 'id'])]
#[ORM\UniqueConstraint(name: 'unq_increment_id_channel_id', columns: ['increment_id', 'channel_id'])]
#[ORM\UniqueConstraint(name: 'unq_origin_id_channel_id', columns: ['origin_id', 'channel_id'])]
class Order extends BaseOrder implements
    ChannelAwareInterface,
    FirstNameInterface,
    LastNameInterface,
    IntegrationAwareInterface,
    OriginAwareInterface,
    ExtendEntityInterface
{
    const STATUS_CANCELED  = 'canceled';
    const STATUS_COMPLETED = 'completed';

    use IntegrationEntityTrait, NamesAwareTrait, ChannelEntityTrait, OriginTrait, ExtendEntityTrait;

    /**
     * Mage entity origin id
     * Should not be used as identity because incrementId is already used
     *
     * @var integer
     */
    #[ORM\Column(name: 'origin_id', type: 'integer', options: ['unsigned' => true], nullable: true)]
    protected $originId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'increment_id', type: 'string', length: 60, nullable: false)]
    #[ConfigField(defaultValues: ['importexport' => ['identity' => true]])]
    protected $incrementId;

    #[ORM\ManyToOne(targetEntity: 'Customer', inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    protected ?BasePerson $customer = null;

    #[ORM\OneToMany(targetEntity: 'OrderAddress', mappedBy: 'owner', cascade: ['all'], orphanRemoval: true)]
    #[ConfigField(defaultValues: ['importexport' => ['full' => true]])]
    protected ?Collection $addresses = null;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'CreditMemo', mappedBy: 'order', cascade: ['all'])]
    #[ConfigField(defaultValues: ['importexport' => ['full' => false]])]
    protected $creditMemos;

    /**
     * @var Store
     */
    #[ORM\ManyToOne(targetEntity: 'Oro\Bundle\MagentoBundle\Entity\Store')]
    #[ORM\JoinColumn(name: 'store_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['importexport' => ['full' => false]])]
    protected $store;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'is_virtual', type: 'boolean', nullable: true)]
    protected $isVirtual = false;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'is_guest', type: 'boolean', nullable: true)]
    protected $isGuest = false;

    /**
     * @var string
     */
    #[ORM\Column(name: 'gift_message', type: 'string', length: 255, nullable: true)]
    protected $giftMessage;

    /**
     * @var string
     */
    #[ORM\Column(name: 'remote_ip', type: 'string', length: 255, nullable: true)]
    protected $remoteIp;

    /**
     * @var string
     */
    #[ORM\Column(name: 'store_name', type: 'string', length: 255, nullable: true)]
    protected $storeName;

    /**
     * @var float
     */
    #[ORM\Column(name: 'total_paid_amount', type: 'float', nullable: true)]
    protected $totalPaidAmount = 0;

    /**
     * @var double
     */
    #[ORM\Column(name: 'total_invoiced_amount', type: 'money', nullable: true)]
    protected $totalInvoicedAmount = 0;

    /**
     * @var double
     */
    #[ORM\Column(name: 'total_refunded_amount', type: 'money', nullable: true)]
    protected $totalRefundedAmount = 0;

    /**
     * @var double
     */
    #[ORM\Column(name: 'total_canceled_amount', type: 'money', nullable: true)]
    protected $totalCanceledAmount = 0;

    #[ORM\ManyToOne(targetEntity: 'Cart')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    protected $cart;

    #[ORM\OneToMany(targetEntity: 'OrderItem', mappedBy: 'order', cascade: ['all'])]
    #[ConfigField(defaultValues: ['importexport' => ['full' => true]])]
    protected ?Collection $items = null;

    /**
     * @var string
     */
    #[ORM\Column(name: 'notes', type: 'text', nullable: true)]
    protected $notes;

    /**
     * @var string
     */
    #[ORM\Column(name: 'feedback', type: 'text', nullable: true)]
    protected $feedback;

    /**
     * @var string
     */
    #[ORM\Column(name: 'customer_email', type: 'string', length: 255, nullable: true)]
    protected $customerEmail;

    /**
     * @var User
     */
    #[ORM\ManyToOne(targetEntity: 'Oro\Bundle\UserBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'user_owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected $owner;

    /**
     * @var Organization
     */
    #[ORM\ManyToOne(targetEntity: 'Oro\Bundle\OrganizationBundle\Entity\Organization')]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected $organization;

    /**
     * @var string
     */
    #[ORM\Column(name: 'coupon_code', type: 'string', length: 255, nullable: true)]
    protected $couponCode;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', name: 'imported_at', nullable: true)]
    protected $importedAt;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', name: 'synced_at', nullable: true)]
    protected $syncedAt;

    /**
     * @var OrderNote[]|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: 'OrderNote', mappedBy: 'order', cascade: ['all'])]
    #[ConfigField(defaultValues: ['importexport' => ['full' => true]])]
    protected $orderNotes;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->creditMemos = new ArrayCollection();
        $this->orderNotes  = new ArrayCollection();
    }

    /**
     * @param string $incrementId
     *
     * @return Order
     */
    public function setIncrementId($incrementId)
    {
        $this->incrementId = $incrementId;

        return $this;
    }

    /**
     * @return string
     */
    public function getIncrementId()
    {
        return $this->incrementId;
    }

    /**
     * @param string $giftMessage
     *
     * @return Order
     */
    public function setGiftMessage($giftMessage)
    {
        $this->giftMessage = $giftMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getGiftMessage()
    {
        return $this->giftMessage;
    }

    /**
     * @param boolean $isGuest
     *
     * @return Order
     */
    public function setIsGuest($isGuest)
    {
        $this->isGuest = $isGuest;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsGuest()
    {
        return $this->isGuest;
    }

    /**
     * @param boolean $isVirtual
     *
     * @return Order
     */
    public function setIsVirtual($isVirtual)
    {
        $this->isVirtual = $isVirtual;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsVirtual()
    {
        return $this->isVirtual;
    }

    /**
     * @param Store $store
     *
     * @return Order
     */
    public function setStore(Store $store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * @return Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param string $storeName
     *
     * @return Order
     */
    public function setStoreName($storeName)
    {
        $this->storeName = $storeName;

        return $this;
    }

    /**
     * @return string
     */
    public function getStoreName()
    {
        return $this->storeName;
    }

    /**
     * @param float $totalCanceledAmount
     *
     * @return Order
     */
    public function setTotalCanceledAmount($totalCanceledAmount)
    {
        $this->totalCanceledAmount = $totalCanceledAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalCanceledAmount()
    {
        return $this->totalCanceledAmount;
    }

    /**
     * @param float $totalInvoicedAmount
     *
     * @return Order
     */
    public function setTotalInvoicedAmount($totalInvoicedAmount)
    {
        $this->totalInvoicedAmount = $totalInvoicedAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalInvoicedAmount()
    {
        return $this->totalInvoicedAmount;
    }

    /**
     * @param float $totalPaidAmount
     *
     * @return Order
     */
    public function setTotalPaidAmount($totalPaidAmount)
    {
        $this->totalPaidAmount = $totalPaidAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalPaidAmount()
    {
        return $this->totalPaidAmount;
    }

    /**
     * @param float $totalRefundedAmount
     *
     * @return Order
     */
    public function setTotalRefundedAmount($totalRefundedAmount)
    {
        $this->totalRefundedAmount = $totalRefundedAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalRefundedAmount()
    {
        return $this->totalRefundedAmount;
    }

    /**
     * @param string $remoteIp
     *
     * @return Order
     */
    public function setRemoteIp($remoteIp)
    {
        $this->remoteIp = $remoteIp;

        return $this;
    }

    /**
     * @return string
     */
    public function getRemoteIp()
    {
        return $this->remoteIp;
    }

    /**
     * @param Cart $cart
     *
     * @return Order
     */
    public function setCart($cart = null)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param string $notes
     *
     * @return Order
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string $feedback
     *
     * @return Order
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;

        return $this;
    }

    /**
     * @return string
     */
    public function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * Pre persist event listener
     */
    #[ORM\PrePersist]
    public function beforeSave()
    {
        $this->updateNames();
    }

    /**
     * Pre update event handler
     */
    #[ORM\PreUpdate]
    public function doPreUpdate()
    {
        $this->updateNames();
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress()
    {
        $addresses = $this->getAddresses()->filter(
            function (AbstractTypedAddress $address) {
                return $address->hasTypeWithName(AddressType::TYPE_BILLING);
            }
        );

        return $addresses->first();
    }

    /**
     * @param string $customerEmail
     *
     * @return Order
     */
    public function setCustomerEmail($customerEmail)
    {
        $this->customerEmail = $customerEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $user
     */
    public function setOwner(User $user)
    {
        $this->owner = $user;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     * @return Order
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @return string
     */
    public function getCouponCode()
    {
        return $this->couponCode;
    }

    /**
     * @param string $couponCode
     * @return Order
     */
    public function setCouponCode($couponCode)
    {
        $this->couponCode = $couponCode;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return strtolower($this->status) === self::STATUS_CANCELED;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return strtolower($this->status) === self::STATUS_COMPLETED;
    }

    /**
     * @return \DateTime
     */
    public function getSyncedAt()
    {
        return $this->syncedAt;
    }

    /**
     * @param \DateTime|null $syncedAt
     * @return Order
     */
    public function setSyncedAt(\DateTime $syncedAt = null)
    {
        $this->syncedAt = $syncedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getImportedAt()
    {
        return $this->importedAt;
    }

    /**
     * @param \DateTime|null $importedAt
     * @return Order
     */
    public function setImportedAt(\DateTime $importedAt = null)
    {
        $this->importedAt = $importedAt;

        return $this;
    }

    /**
     * @param ArrayCollection|CreditMemo[] $creditMemos
     *
     * @return Order
     */
    public function resetCreditMemos($creditMemos)
    {
        $this->creditMemos->clear();

        foreach ($creditMemos as $creditMemo) {
            $this->addCreditMemo($creditMemo);
        }

        return $this;
    }

    /**
     * @param CreditMemo $creditMemo
     *
     * @return Order
     */
    public function addCreditMemo(CreditMemo $creditMemo)
    {
        if (!$this->creditMemos->contains($creditMemo)) {
            $this->creditMemos->add($creditMemo);
        }

        return $this;
    }

    /**
     * @param CreditMemo $creditMemo
     *
     * @return Order
     */
    public function removeCreditMemo(CreditMemo $creditMemo)
    {
        if ($this->creditMemos->contains($creditMemo)) {
            $this->creditMemos->removeElement($creditMemo);
        }

        return $this;
    }

    /**
     * @return ArrayCollection|CreditMemo[]
     */
    public function getCreditMemos()
    {
        return $this->creditMemos;
    }

    /**
     * @param CreditMemo $creditMemo
     *
     * @return bool
     */
    public function hasCreditMemo(CreditMemo $creditMemo)
    {
        return $this->getCreditMemos()->contains($creditMemo);
    }

    /**
     * @return ArrayCollection|OrderNote[]
     */
    public function getOrderNotes()
    {
        return $this->orderNotes;
    }

    /**
     * @param OrderNote $orderNote
     *
     * @return Order
     */
    public function addOrderNote(OrderNote $orderNote)
    {
        if (!$this->hasOrderNote($orderNote)) {
            $this->orderNotes->add($orderNote);
            $orderNote->setOrder($this);
        }

        return $this;
    }

    /**
     * @param OrderNote $orderNote
     *
     * @return Order
     */
    public function removeOrderNote(OrderNote $orderNote)
    {
        if ($this->hasOrderNote($orderNote)) {
            $this->orderNotes->removeElement($orderNote);
        }

        return $this;
    }

    /**
     * @param OrderNote $orderNote
     *
     * @return bool
     */
    public function hasOrderNote(OrderNote $orderNote)
    {
        return $this->orderNotes->contains($orderNote);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getIncrementId();
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        parent::__clone();

        if ($this->orderNotes) {
            $this->orderNotes = clone $this->orderNotes;
        }

        if ($this->creditMemos) {
            $this->creditMemos = clone $this->creditMemos;
        }
    }
}
