<?php
 
namespace Orienteed\GraphQl\Model\Resolver\Mutation;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Mageplaza\SaveCart\Helper\Data;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Mageplaza\SaveCart\Api\SaveCartRepositoryInterface;
 
class RestoreCart extends \Mageplaza\SaveCartGraphQl\Model\Resolver\Mutation\RestoreCart
{
    /**
     * @var SaveCartRepositoryInterface
     */
    private $saveCartRepository;

    public function __construct(
        Data $helperData,
        GetCustomer $getCustomer,
        SaveCartRepositoryInterface $saveCartRepository,
        \Magento\Quote\Model\MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId
    ) {
        $this->saveCartRepository = $saveCartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        parent::__construct($helperData, $getCustomer, $saveCartRepository);
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['cart_id'])) {
            throw new GraphQlInputException(__('"cart_id" value should be specified'));
        }
        $customer = $this->getCustomer->execute($context);

        $cartId = $this->maskedQuoteIdToQuoteId->execute($args['cart_id']);

        try {
            return $this->saveCartRepository->restore((int)$customer->getId(), $cartId, $args['token']);
        } catch (InputException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw new GraphQlAlreadyExistsException(__($e->getMessage()));
        }
    }
}