<?php

namespace Lighthouse\CoreBundle\Test\Factory\Receipt;

use Lighthouse\CoreBundle\Document\Payment\BankCardPayment;
use Lighthouse\CoreBundle\Document\StockMovement\Receipt;
use Lighthouse\CoreBundle\Document\StockMovement\ReceiptRepository;
use Lighthouse\CoreBundle\Document\StockMovement\Returne\ReturnProduct;
use Lighthouse\CoreBundle\Document\StockMovement\Returne\Returne;
use Lighthouse\CoreBundle\Document\StockMovement\Sale\SaleProduct;
use Lighthouse\CoreBundle\Document\StockMovement\Sale\Sale;
use Lighthouse\CoreBundle\Document\StockMovement\WriteOff\WriteOffRepository;
use Lighthouse\CoreBundle\Document\Store\Store;
use Lighthouse\CoreBundle\Test\Factory\Factory;
use Lighthouse\CoreBundle\Types\Date\DateTimestamp;
use Lighthouse\CoreBundle\Types\Numeric\NumericFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReceiptBuilder
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var WriteOffRepository
     */
    protected $repository;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var NumericFactory
     */
    protected $numericFactory;

    /**
     * @var Receipt
     */
    protected $receipt;

    /**
     * @param Factory $factory
     * @param ReceiptRepository $receiptRepository
     * @param ValidatorInterface $validator
     * @param NumericFactory $numericFactory
     */
    public function __construct(
        Factory $factory,
        ReceiptRepository $receiptRepository,
        ValidatorInterface $validator,
        NumericFactory $numericFactory
    ) {
        $this->factory = $factory;
        $this->repository = $receiptRepository;
        $this->validator = $validator;
        $this->numericFactory = $numericFactory;
    }

    /**
     * @param Store $store
     * @param string $date
     * @return ReceiptBuilder
     */
    public function createSale(Store $store = null, $date = null)
    {
        return $this->populateReceipt(Sale::TYPE, $store, $date);
    }

    /**
     * @param Store $store
     * @param string $date
     * @param Sale $sale
     * @return ReceiptBuilder
     */
    public function createReturn(Store $store = null, $date = null, $sale = null)
    {
        return $this->populateReceipt(Returne::TYPE, $store, $date, $sale);
    }

    /**
     * @param string $type
     * @param Store $store
     * @param string $date
     * @param string $hash
     * @param Sale $sale
     * @return ReceiptBuilder
     */
    protected function populateReceipt($type, Store $store = null, $date = null, $sale = null, $hash = null)
    {
        $receipt = $this->repository->createNewByType($type);

        $date = ($date) ?: new \DateTime();

        $store = ($store) ?: $this->factory->store()->getStore();

        $this->receipt = $receipt;
        $this->receipt->store = $store;
        $this->receipt->date = new DateTimestamp($date);

        if (null != $sale) {
            $this->receipt->sale = $sale;
        }

        $this->receipt->hash = ($hash) ?: md5($store->id . ':' . $this->receipt->date->format(DateTimestamp::RFC3339));

        return $this;
    }

    /**
     * @param string $productId
     * @param float $quantity
     * @param float $price
     * @return ReceiptBuilder
     */
    public function createReceiptProduct($productId, $quantity = null, $price = null)
    {
        $receiptProduct = ($this->receipt instanceof Sale) ? new SaleProduct() : new ReturnProduct();

        $quantity = ($quantity) ?: 1;
        $price = ($price) ?: 5.99;

        $receiptProduct->parent = $this->receipt;
        $receiptProduct->product = $this->factory->createProductVersion($productId);
        $receiptProduct->quantity = $this->numericFactory->createQuantity($quantity);
        $receiptProduct->price = $this->numericFactory->createMoney($price);

        $this->receipt->products->add($receiptProduct);

        return $this;
    }

    /**
     * @return ReceiptFactory
     * @throws \InvalidArgumentException
     */
    public function persist()
    {
        $this->preValidate();

        $this->validator->validate($this->receipt);
        $this->repository->getDocumentManager()->persist($this->receipt);
        return $this->factory->receipt();
    }

    protected function preValidate()
    {
        $this->receipt->prePersist();
        $this->receipt->calculateTotals();

        if ($this->receipt instanceof Sale && !$this->receipt->payment) {
            $this->receipt->payment = new BankCardPayment();
        }
    }

    /**
     * @return Receipt|Sale|Returne
     * @throws \InvalidArgumentException
     */
    public function flush()
    {
        $this->persist();
        $this->repository->getDocumentManager()->flush();
        return $this->receipt;
    }
}
