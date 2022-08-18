<?php


class Controller
{
    public function __construct(private Repository $repository)
    {
    }
    
    public function index()
    {
        $addressTypes = $this->repository->getAddressTypes();
        $deliveryTypes = $this->repository->getDeliveryTypes();
        $addressObjectTypes = $this->repository->getAddressObjectTypes();
        View::render('index', compact('addressTypes', 'addressObjectTypes', 'deliveryTypes'));
    }
    
    public function objectTypeValues(string $addressObjectTypeCode)
    {
        $types = $this->repository->getAddressObjectTypeValues($addressObjectTypeCode);
        View::render('address-object-type-values', compact('types'));
    }
    
    public function addressesForPickupPoints()
    {
        $addresses = $this->repository->getAvailableAddressesForPickupPoint();
        View::render('addresses-for-pickup-points', compact('addresses'));
    }
    
    public function getAddressFields(int $addressId)
    {
        $addressTypes = $this->repository->getAddressTypes();
        $addressObjectTypes = $this->repository->getAddressObjectTypes();
        $addressValues = [];
        foreach ($this->repository->getAddressDisplayValues($addressId) as $value) {
            $addressValues[$value['code']] = $value;
        }
        $address = $this->repository->getAddress($addressId);
        View::render('address-fields', compact('addressTypes', 'addressObjectTypes', 'addressValues', 'address'));
    }
    
    public function createAddress(?string $addressId, string $addressTypeCode, array $address)
    {
        echo $this->repository->createAddress($addressId ? (int)$addressId : null, $addressTypeCode, $address);
    }
    
    public function changeDpdDeliveryForRiga()
    {
        $this->repository->changeDpdDeliveryForRiga();
        echo 'OK';
    }
    
    public function addDeliveryConfiguration()
    {
        $this->repository->createDeliveryConfigurationsForDpdLithuania();
        echo 'OK';
    }
    
    public function normalizePhones()
    {
        $this->repository->normalizeContactPhones();
        echo 'OK';
    }
    
    public function orderNumber()
    {
        echo $this->repository->getOrderNumber();
    }
    
    public function deleteDuplicatePhones()
    {
        $this->repository->deleteDuplicateContactPhones();
        echo 'OK';
    }
    
    public function addressDisplayValues(int $addressId)
    {
        $values = $this->repository->getAddressDisplayValues($addressId);
        View::render('address-display-values', compact('values'));
    }
    
    public function deliveryPointsCountField()
    {
        $points = $this->repository->getDeliveryPointsCountForOmniva();
        View::render('delivery-point-count', compact('points'));
    }
    
    public function deliveryFee(string $deliveryTypeCode, ?string $addressTypeCode = null, ?string $orderAmount = null, ?string $orderWeight = null)
    {
        echo $this->repository->getDeliveryFee(
            $deliveryTypeCode,
            $addressTypeCode,
            $orderAmount === '' ? null : (float)$orderAmount,
            $orderWeight === '' ? null : (float)$orderWeight
        );
    }
    
    public function deliveryFeeWithDayCondition(string $deliveryTypeCode, ?string $addressTypeCode = null)
    {
        echo $this->repository->getDeliveryFeeForSaturday($deliveryTypeCode, $addressTypeCode);
    }
}
