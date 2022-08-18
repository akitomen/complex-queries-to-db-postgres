<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>1a test postgreSQL with laravel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link href="/app.css" type="text/css" rel="stylesheet">
</head>
<body>

<div class="container overflow-hidden">
    <div class="row gy-5">
        <div class="col-12">
            <div class="p-3">
                <h2>1 query section</h2>
                <span>Create SQL query which will select all possible address object type values for given address object type
                (example : enter CITY or COUNTRY )</span>
                <hr>
                <form action="{{ route('objectTypeValues') }}" method="post" data-field="addressObjectTypeField">
                    <div class="mb-3">
                        <label for="addressObjectTypeCode" class="form-label">Address Object Type Code</label>
                        <input type="text" class="form-control" id="addressObjectTypeCode" name="addressObjectTypeCode"
                               required>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
                <div class="container">
                    <div class="row" id="addressObjectTypeField"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-5">
        <div class="col-12">
            <div class="p-3">
                <h2>2 query section</h2>
                <span>Create SQL query which will select all possible delivery addresses for pickup point delivery types</span>
                <hr>
                <form action="{{ route('addressesForPickupPoints') }}" method="post" data-field="addressesForPickupPointsField">
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
                <div class="container">
                    <div class="row" id="addressesForPickupPointsField"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-5">
        <div class="col-12">
            <div class="p-3">
                <h2>3 query section</h2>
                <span>Create PHP class or function which will create and edit user inputted address</span>
                <hr>
                <form action="{{ route('createAddress') }}" method="post" data-field="createAddress" id="createAddressForm">
                    <div class="mb-2">
                        <label for="addressId" class="form-label">Address Id (1-1700)</label>
                        <input type="number" class="form-control" id="addressId" name="addressId">
                    </div>
                    <div class="address-fields">
                        @include('tables.address-fields')
                    </div>

                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
                <div class="container">
                    <div class="row">
                        <div class="p-3" id="createAddress"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-5">
        <div class="col-12">
            <div class="p-3">
                <h2>4 query section</h2>
                <span>Create SQL query which will select address display values where each address object type is row</span>
                <hr>
                <form action="{{ route('addressDisplayValues') }}" method="post" data-field="addressDisplayValuesField">
                    <div class="mb-3">
                        <label for="addressId" class="form-label">Address Id</label>
                        <input type="text" class="form-control" id="addressId" name="addressId" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
                <div class="container">
                    <div class="row" id="addressDisplayValuesField"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-5">
        <div class="col-12">
            <div class="p-3">
                <h2>5 query section</h2>
                <span>Create PHP class or function which will build Full Address for inputted address</span>
                <hr>
                <span>Method Repository::setFullAddress() is used by address creating from 3 query section</span>
            </div>
        </div>
    </div>

    <div class="row gy-5">
        <div class="col-12">
            <div class="p-3">
                <h2>6 query section</h2>
                <span>Create SQL query which will select delivery fee for delivery type, order amount, order weight and for specific address type value</span>
                <hr>
                <form action="{{ route('deliveryFee') }}" method="post" data-field="deliveryFeeField">
                    <div class="mb-3">
                        <label for="deliveryTypeCode" class="form-label">Delivery Type Code</label>
                        <select id="deliveryTypeCode" name="deliveryTypeCode" required class="form-select">
                            @foreach($deliveryTypes as $deliveryType)
                                <option value="{{ $deliveryType->code }}">{{ $deliveryType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="addressTypeCode" class="form-label">Address Type Code</label>
                        <select id="addressTypeCode" name="addressTypeCode" class="form-select">
                            @foreach($addressTypes as $addressType)
                                <option value="{{ $addressType->code }}">{{ $addressType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="orderAmount" class="form-label">Order Amount</label>
                        <input type="number" class="form-control" id="orderAmount" name="orderAmount">
                    </div>
                    <div class="mb-3">
                        <label for="orderWeight" class="form-label">Order Weight</label>
                        <input type="number" class="form-control" id="orderWeight" name="orderWeight">
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
                <div class="container">
                    <div class="row">
                        <div class="p-3" id="deliveryFeeField"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-5">
        <div class="col-12">
            <div class="p-3">
                <h2>7 query section</h2>
                <span>Change delivery fee configuration so that DPD delivery for Riga will be 5 EUR cheaper than current delivery fee for DPD</span>
                <hr>
                <form action="{{ route('changeDpdDeliveryForRiga') }}" method="post" data-field="changeDpdDeliveryForRigaField">
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
                <div class="container">
                    <div class="row">
                        <div class="p-3" id="changeDpdDeliveryForRigaField"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-5">
        <div class="col-12">
            <div class="p-3">
                <h2>8 query section</h2>
                <span>Change delivery fee configuration for extra 3 EUR charge for deliveries on Saturday</span>
                <hr>
                <form action="{{ route('deliveryFeeWithDayCondition') }}" method="post"
                      data-field="deliveryFeeWithDayConditionField">
                    <div class="mb-3">
                        <label for="deliveryTypeCode" class="form-label">Delivery Type Code</label>
                        <select id="deliveryTypeCode" name="deliveryTypeCode" required class="form-select">
                            @foreach($deliveryTypes as $deliveryType)
                                <option value="{{ $deliveryType->code }}">{{ $deliveryType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="addressTypeCode" class="form-label">Address Type Code</label>
                        <select id="addressTypeCode" name="addressTypeCode" class="form-select">
                            @foreach($addressTypes as $addressType)
                                <option value="{{ $addressType->code }}">{{ $addressType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
                <div class="container">
                    <div class="row">
                        <div class="p-3" id="deliveryFeeWithDayConditionField"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-5">
        <div class="col-12">
            <div class="p-3">
                <h2>9 query section</h2>
                <span>Create SQL query to retrieve Pickup Point count for Omniva grouped by city</span>
                <hr>
                <form action="{{ route('deliveryPointsCount') }}" method="post" data-field="deliveryPointsCountField">
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
                <div class="container">
                    <div class="row" id="deliveryPointsCountField"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-5">
        <div class="col-12">
            <div class="p-3">
                <h2>10 query section</h2>
                <span>Create delivery fee configuration for delivery to Lithuania shipped by DPD with fee 10 EUR if order weight is less than 10kg and 20 EUR otherwise</span>
                <hr>
                <form action="{{ route('addDeliveryConfiguration') }}" method="post" data-field="addDeliveryConfigurationField">
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
                <div class="container">
                    <div class="row">
                        <div class="p-3" id="addDeliveryConfigurationField"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-5">
        <div class="col-12">
            <div class="p-3">
                <h2>11 query section</h2>
                <span>Create SQL which will normalize phone numbers in table contacts by removing country code. Country code is predefined 371</span>
                <hr>
                <form action="{{ route('normalizePhones') }}" method="post" data-field="normalizePhonesField">
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
                <div class="container">
                    <div class="row">
                        <div class="p-3" id="normalizePhonesField"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-5">
        <div class="col-12">
            <div class="p-3">
                <h2>12 query section</h2>
                <span>Eliminate normalized phone number duplicates in table contacts. Leave most recent contact (sorting by ID descending)</span>
                <hr>
                <form action="{{ route('deleteDuplicatePhones') }}" method="post" data-field="deleteDuplicatePhonesField">
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
                <div class="container">
                    <div class="row">
                        <div class="p-3" id="deleteDuplicatePhonesField"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row gy-5">
        <div class="col-12">
            <div class="p-3">
                <h2>13 query section</h2>
                <span>Assume you have orders table and there is a requirement to generate unique order numbers which can’t have gaps. Create needed database structures and PHP class or function to generate unique order numbers without gaps</span>
                <hr>
                <form action="{{ route('orderNumber') }}" method="post" data-field="orderNumberField">
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </form>
                <div class="container">
                    <div class="row">
                        <div class="p-3" id="orderNumberField"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"
        integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
<script src="app.js" type="text/javascript"></script>
</body>
</html>

