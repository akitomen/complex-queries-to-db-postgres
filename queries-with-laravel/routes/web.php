<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [Controller::class, 'index'])->name('index');
Route::post('object-type-values', [Controller::class, 'objectTypeValues'])->name('objectTypeValues');
Route::post('addresses-for-pickup-points', [Controller::class, 'addressesForPickupPoints'])->name('addressesForPickupPoints');
Route::post('create-address', [Controller::class, 'createAddress'])->name('createAddress');
Route::post('get-address-fields', [Controller::class, 'getAddressFields'])->name('getAddressFields');
Route::post('address-display-values', [Controller::class, 'addressDisplayValues'])->name('addressDisplayValues');
Route::post('delivery-fee', [Controller::class, 'deliveryFee'])->name('deliveryFee');
Route::post('change-dpd-delivery-for-riga', [Controller::class, 'changeDpdDeliveryForRiga'])->name('changeDpdDeliveryForRiga');
Route::post('delivery-fee-with-day-condition', [Controller::class, 'deliveryFeeWithDayCondition'])->name('deliveryFeeWithDayCondition');
Route::post('delivery-point-count', [Controller::class, 'deliveryPointsCount'])->name('deliveryPointsCount');
Route::post('add-delivery-configuration', [Controller::class, 'addDeliveryConfiguration'])->name('addDeliveryConfiguration');
Route::post('normalize-phones', [Controller::class, 'normalizePhones'])->name('normalizePhones');
Route::post('delete-duplicate-phones', [Controller::class, 'deleteDuplicatePhones'])->name('deleteDuplicatePhones');
Route::post('order-number', [Controller::class, 'orderNumber'])->name('orderNumber');
