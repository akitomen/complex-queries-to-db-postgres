<?php
return [
    '/' => [Controller::class, 'index'],
    '/object-type-values' => [Controller::class, 'objectTypeValues'],
    '/addresses-for-pickup-points' => [Controller::class, 'addressesForPickupPoints'],
    '/create-address' => [Controller::class, 'createAddress'],
    '/get-address-fields' => [Controller::class, 'getAddressFields'],
    '/address-display-values' => [Controller::class, 'addressDisplayValues'],
    '/delivery-fee' => [Controller::class, 'deliveryFee'],
    '/change-dpd-delivery-for-riga' => [Controller::class, 'changeDpdDeliveryForRiga'],
    '/delivery-fee-with-day-condition' => [Controller::class, 'deliveryFeeWithDayCondition'],
    '/delivery-point-count' => [Controller::class, 'deliveryPointsCountField'],
    '/add-delivery-configuration' => [Controller::class, 'addDeliveryConfiguration'],
    '/normalize-phones' => [Controller::class, 'normalizePhones'],
    '/delete-duplicate-phones' => [Controller::class, 'deleteDuplicatePhones'],
    '/order-number' => [Controller::class, 'orderNumber'],
];
