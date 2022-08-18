<?php

namespace App\Http\Controllers;

use App\Repositories\PostgresRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function __construct(private PostgresRepository $repository)
    {
    }

    public function index(): Factory|View|Application
    {
        $addressTypes = $this->repository->getAddressTypes();
        $deliveryTypes = $this->repository->getDeliveryTypes();
        $addressObjectTypes = $this->repository->getAddressObjectTypes();
        return view('index', compact('addressTypes', 'addressObjectTypes', 'deliveryTypes'));
    }

    public function objectTypeValues(): Factory|View|Application
    {
        $types = $this->repository->getAddressObjectTypeValues(request()->post('addressObjectTypeCode'));
        return view('tables.object-type-values', compact('types'));
    }


    public function addressesForPickupPoints(): Factory|View|Application
    {
        $addresses = $this->repository->getAvailableAddressesForPickupPoint();
        return view('tables.addresses-for-pickup-points', compact('addresses'));
    }

    public function createAddress(): int
    {
        return $this->repository->createAddress(request()->post('addressId') ?? null, request()->post('addressTypeCode'), request()->post('address'));
    }

    public function getAddressFields(): Factory|View|Application
    {
        $addressTypes = $this->repository->getAddressTypes();
        $addressObjectTypes = $this->repository->getAddressObjectTypes();
        $addressValues = [];
        foreach ($this->repository->getAddressDisplayValues(request()->post('addressId')) as $value) {
            $addressValues[$value->code] = $value;
        }
        $address = $this->repository->getAddress(request()->post('addressId'));
        return view('tables.address-fields', compact('addressTypes', 'addressObjectTypes', 'addressValues', 'address'));
    }

    public function changeDpdDeliveryForRiga(): string
    {
        $this->repository->changeDpdDeliveryForRiga();
        return 'OK';
    }

    public function addDeliveryConfiguration(): string
    {
        $this->repository->createDeliveryConfigurationsForDpdLithuania();
        return 'OK';
    }

    public function normalizePhones(): string
    {
        $this->repository->normalizeContactPhones();
        return 'OK';
    }

    public function orderNumber(): int
    {
        return $this->repository->getOrderNumber();
    }

    public function deleteDuplicatePhones(): string
    {
        $this->repository->deleteDuplicateContactPhones();
        return 'OK';
    }

    public function addressDisplayValues(): Factory|View|Application
    {
        $values = $this->repository->getAddressDisplayValues(request()->post('addressId'));
        return view('tables.address-display-values', compact('values'));
    }

    public function deliveryPointsCount(): Factory|View|Application
    {
        $points = $this->repository->getDeliveryPointsCountForOmniva();
        return view('tables.delivery-point-count', compact('points'));
    }

    public function deliveryFee(): float
    {
        return $this->repository->getDeliveryFee(
            request()->post('deliveryTypeCode'),
            request()->post('addressTypeCode'),
            request()->post('orderAmount') === '' ? null : (float)request()->post('orderAmount'),
            request()->post('orderWeight') === '' ? null : (float)request()->post('orderWeight')
        );
    }

    public function deliveryFeeWithDayCondition(): float
    {
        return $this->repository->getDeliveryFeeForSaturday(request()->post('deliveryTypeCode'), request()->post('addressTypeCode'));
    }
}
