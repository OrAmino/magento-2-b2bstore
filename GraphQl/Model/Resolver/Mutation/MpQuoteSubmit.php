<?php

declare(strict_types=1);

namespace Orienteed\GraphQl\Model\Resolver\Mutation;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Mageplaza\RequestForQuote\Model\CartQuote;
use Mageplaza\RequestForQuote\Model\Config\Source\Status;
use Mageplaza\RequestForQuote\Helper\Data;
use Mageplaza\RequestForQuote\Helper\Email as EmailHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

class MpQuoteSubmit implements ResolverInterface
{
    /**
     * @var CartQuote
     */
    private $cartQuote;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * SaveCart constructor.
     *
     * @param Data $helperData
     * @param GetCustomer $getCustomer
     * @param SaveCartRepositoryInterface $saveCartRepository
     */
    public function __construct(
        CartQuote $cartQuote,
        EmailHelper $emailHelper,
        Data $helperData,
        Session $customerSession
    ) {
        $this->cartQuote = $cartQuote;
        $this->emailHelper = $emailHelper;
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $customerId = $context->getUserId();
        $this->customerSession->setCustomerId($customerId);

        $status      = Status::STATUS_PENDING;
        $cartQuote   = $this->cartQuote->getQuoteCart();

        if (!$cartQuote->getId()) {
            throw new GraphQlAuthorizationException(__('Cart Quote not found.'));
        }

        $autoApprove = $this->helperData->autoApprove($cartQuote);

        if ($autoApprove) {
            $status = Status::STATUS_APPROVED;
        }

        $cartQuote->setIsActive(1)->setStatus($status);
        $cartQuote->save();

        /** send email */
        if ($this->emailHelper->getConfigEmail('new_update/is_enable_submit')) {
            $this->emailHelper->sendEmail(
                $this->emailHelper->getSubmitTemplate(),
                $cartQuote->getCustomerEmail(),
                $this->emailHelper->getTemplateParams($cartQuote)
            );
        }

        return $cartQuote->getId();
    }
}
