<?php


namespace App\Repositories;


use App\Repositories\Interfaces\PostgresRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use stdClass;

class PostgresRepository implements PostgresRepositoryInterface
{
    // 1
    public function getAddressObjectTypeValues(string $addressObjectTypeCode): array
    {
        return DB::select(
            'select aotv.*
            from address_object_type_values aotv
                     inner join address_object_types aot on aot.id = aotv.aot_id
            where aot.code = ? order by aotv.value',
            [$addressObjectTypeCode]
        );
    }

    // 2
    public function getAvailableAddressesForPickupPoint(): array
    {
        return DB::select(
            'select distinct a.*
            from addresses a
                     inner join address_types at on at.id = a.atp_id
                     inner join delivery_type_available_address_types dtaat on at.id = dtaat.atp_id
                     inner join delivery_types dt on dt.id = dtaat.dty_id
            where dt.type = ? order by a.full_address',
            ['PICKUP_POINT']
        );
    }

    // 3
    public function createAddress(?int $addressId, string $addressTypeCode, array $values): int
    {
        $addressObjectTypes = [];
        foreach (
            DB::select(
                'select aot.id, aot.code, is_free_text from address_object_types aot
            inner join address_types at on at.id = aot.atp_id
                where at.code = ? and aot.code = any (?)',
                [$addressTypeCode, '{' . implode(', ', array_keys($values)) . '}']
            ) as $addressObjectType
        ) {
            $addressObjectTypes[$addressObjectType->code] = [
                'id' => (int)$addressObjectType->id,
                'is_free_text' => $addressObjectType->is_free_text
            ];
        }

        DB::beginTransaction();

        if ($addressId) {
            if (DB::update(
                    'update addresses a
                set atp_id = (select atp.id from address_types atp where upper(atp.code) = upper(?))
                where a.id = ?',
                    [$addressTypeCode, $addressId]
                ) <= 0 ||
                DB::delete(
                    'delete
                from address_objects
                where addr_id = ?',
                    [$addressId]
                ) <= 0) {
                DB::rollBack();
                throw new Exception("Address updating error");
            }
        } else {
            $address = DB::selectOne(
                'insert into addresses (id, atp_id)
            values (nextval(\'addr_seq\'), (select id from address_types where upper(code) = upper(?)))
            returning id',
                [$addressTypeCode]
            );
            $addressId = $address->id;
        }

        foreach ($values as $addressObjectTypeCode => $addressObjectTypeValue) {
            if (! empty($addressObjectTypeValue)) {
                if ($addressObjectTypes[$addressObjectTypeCode]['is_free_text']) {
                    $res = DB::insert(
                        'insert into address_objects (addr_id, aot_id, value)
                values (?, ?, ?)',
                        [
                            $addressId,
                            $addressObjectTypes[$addressObjectTypeCode]['id'],
                            $addressObjectTypeValue
                        ]
                    );
                } else {
                    if ($type = DB::selectOne(
                        'select aov.id
                             from address_types atp
                                      join address_object_types aot on aot.atp_id = atp.id and upper(aot.code) = upper(?)
                                      join address_object_type_values aov on aov.aot_id = aot.id and upper(aov.value) like \'%\' || upper(?) || \'%\'
                             where upper(atp.code) = upper(?)',
                        [
                            $addressObjectTypeCode,
                            $addressObjectTypeValue,
                            $addressTypeCode
                        ]
                    )) {
                        $res = DB::insert(
                            'insert into address_objects (addr_id, aot_id, aov_id)
                    values (?, ?, ?)',
                            [
                                $addressId,
                                $addressObjectTypes[$addressObjectTypeCode]['id'],
                                $type->id
                            ]
                        );
                    } else {
                        DB::rollBack();
                        throw new Exception("Address object type value '$addressObjectTypeValue' not found", 404);
                    }
                }

                if ($res === false) {
                    DB::rollBack();
                    throw new Exception("Address object type value inserting error");
                }
            }
        }
        $this->setFullAddress($addressId);

        DB::commit();

        return $addressId;
    }

    // 4
    public function getAddressDisplayValues(int $addressId): array
    {
        $result = DB::select(
            'select aot.code as code, aot.name as type, (coalesce(aotv.value, ao.value)) as value
            from address_objects ao
                     inner join address_object_types aot on aot.id = ao.aot_id
                     left join address_object_type_values aotv on aotv.id = ao.aov_id
            where ao.addr_id = ?
            order by aot.sequence_number',
            [$addressId]
        );
        if ($result && count($result) > 0) {
            return $result;
        }

        throw new Exception("Address '$addressId' not found", 404);
    }

    // 5
    public function setFullAddress(int $addressId)
    {
        $values = [];
        foreach (
            DB::select(
                'select aotv.value as enum_value, ao.value as text_value
            from address_objects ao
                    inner join address_object_types aot on aot.id = ao.aot_id
                    left join address_object_type_values aotv on aotv.id = ao.aov_id
            where ao.addr_id = ?
            order by aot.sequence_number',
                [$addressId]
            ) as $addressObject
        ) {
            $values[] = $addressObject->enum_value ?? $addressObject->text_value;
        }

        if (DB::update(
                'update addresses set full_address = ? where id = ?',
                [implode(', ', array_filter($values)), $addressId]
            ) <= 0) {
            throw new Exception("Address updating error");
        }
    }

    // 6
    public function getDeliveryFee(string $deliveryTypeCode, ?string $addressTypeCode = null, ?float $orderAmount = null, ?float $orderWeight = null): float
    {
        if ($configuration = DB::selectOne(
            'select dfc.delivery_fee
            from delivery_fee_configuration dfc
                     inner join delivery_types dt on dt.id = dfc.dty_id
                     left join address_types at on at.id = dfc.atp_id
            where dt.code = ?
              and (dfc.atp_id is null or at.code = ?)
              and (
                    dfc.total_product_weight_from is null or dfc.total_product_weight_from < ?
                    and dfc.total_product_weight_to is null or dfc.total_product_weight_to >= ?
                )
              and (
                    dfc.order_total_amount_from is null or dfc.order_total_amount_from < ?
                    and dfc.order_total_amount_to is null or dfc.order_total_amount_to >= ?
                ) order by dfc.delivery_fee limit 1',
            [$deliveryTypeCode, $addressTypeCode, $orderWeight, $orderWeight, $orderAmount, $orderAmount]
        )) {
            return $configuration->delivery_fee;
        }

        throw new Exception("Delivery fee for your conditions not found", 404);
    }

    // 7
    public function changeDpdDeliveryForRiga(): bool
    {
        if (! ($addressObjectTypeValue = DB::selectOne(
            'select id from address_object_type_values where value = ?',
            ['Riga']
        ))) {
            if (! ($addressObjectTypeValue = DB::selectOne(
                'insert into address_object_type_values (id, aot_id, value)
                values (nextval(\'aov_seq\'),
                        (select aot.id
                         from address_object_types aot
                                  inner join address_types at on at.id = aot.atp_id
                         where at.code = ?
                           and aot.code = ?),
                        ?) returning id',
                ['LV', 'CITY', 'Riga']
            )
            )) {
                throw new Exception("Adding Riga error");
            }
        }

        if (DB::insert(
            'insert into delivery_fee_configuration (id, dty_id, dft_id, aov_id, delivery_fee)
            values (nextval(\'dfc_seq\'), (select id from delivery_types where code = ?),
                    (select id from delivery_fee_types where code = ?),
                    ?,
                    greatest((select delivery_fee
                     from delivery_fee_configuration
                     where dty_id = (select id from delivery_types where code = ?)
                       and dft_id = (select id from delivery_fee_types where code = ?)
                       and aov_id is null
                       and atp_id is null
                     limit 1) - 5, 0))',
            ['DPD', 'BASE', $addressObjectTypeValue->id, 'DPD', 'BASE']
        )) {
            return true;
        }

        throw new Exception("Delivery configuration updating error");
    }

    // 8
    public function getDeliveryFeeForSaturday(string $deliveryTypeCode, ?string $addressTypeCode = null): float
    {
        if ($configuration = DB::selectOne(
            'select (case when extract(isodow from now()) = 6 then delivery_fee + 3 else delivery_fee end) as delivery_fee
            from delivery_fee_configuration dfc
                     inner join delivery_types dt on dt.id = dfc.dty_id
                     left join address_types at on at.id = dfc.atp_id
            where dt.code = ?
              and (dfc.atp_id is null or at.code = ?)
              order by delivery_fee limit 1',
            [$deliveryTypeCode, $addressTypeCode]
        )) {
            return $configuration->delivery_fee;
        }

        throw new Exception("Delivery fee for your conditions not found", 404);
    }

    // 9
    public function getDeliveryPointsCountForOmniva(): array
    {
        return DB::select(
            'select aotv.value as city, count(pp.id) as count
            from address_object_type_values aotv
                     inner join address_object_types aot on aot.id = aotv.aot_id
                     inner join address_objects ao on aotv.id = ao.aov_id
                     inner join pickup_points pp on ao.addr_id = pp.addr_id
                     inner join delivery_types dt on pp.dty_id = dt.id
            where dt.code = ?
              and aot.code = ?
            group by aotv.value
            order by aotv.value',
            ['OMNIVA_PICKUP', 'CITY']
        );
    }

    // 10
    public function createDeliveryConfigurationsForDpdLithuania(): bool
    {
        if (DB::insert(
                'insert into delivery_fee_configuration(id, dty_id, dft_id, atp_id, total_product_weight_to, delivery_fee)
            values (nextval(\'dfc_seq\'), (select id from delivery_types where code = ?),
                    (select id from delivery_fee_types where code = ?),
                    (select id from address_types where code = ?), ?, ?)',
                ['DPD', 'BASE', 'LT', 10, 10]
            ) && DB::insert(
                'insert into delivery_fee_configuration(id, dty_id, dft_id, atp_id, total_product_weight_from, delivery_fee)
            values (nextval(\'dfc_seq\'), (select id from delivery_types where code = ?),
                    (select id from delivery_fee_types where code = ?),
                    (select id from address_types where code = ?), ?, ?)',
                ['DPD', 'BASE', 'LT', 10, 10]
            )) {
            return true;
        }

        throw new Exception("Query error");
    }

    // 11
    public function normalizeContactPhones(): bool
    {
        if (DB::update(
            'update contacts
            set phone_number = replace(regexp_replace(phone_number, \'^[0+ ]{0,2}371\', \'\'), \' \', \'\')',
        )) {
            return true;
        }

        throw new Exception("Query error");
    }

    // 12
    public function deleteDuplicateContactPhones(): bool
    {
        if (DB::statement(
            'with duplicates as (
                select phone_number from contacts group by phone_number having count(*) > 1
            ),
                 to_del as (
                     select c.id, rank() over (partition by c.phone_number order by c.id desc) rnk
                     from contacts c
                              inner join duplicates d on c.phone_number = d.phone_number
                 )
            delete
            from contacts
            where id in (select id from to_del where rnk > 1)',
        )) {
            return true;
        }

        throw new Exception("Query error");
    }

    // 13
    public function getOrderNumber(): int
    {
        DB::statement('create sequence if not exists order_seq');
        $value = DB::selectOne('select nextval(\'order_seq\') as value');
        return $value->value;
    }

    public function getAddressTypes(): array
    {
        return DB::select('select * from address_types');
    }

    public function getAddressObjectTypes(): array
    {
        return DB::select('select * from address_object_types');
    }

    public function getDeliveryTypes(): array
    {
        return DB::select('select * from delivery_types');
    }

    public function getAddress(int $addressId): stdClass
    {
        if ($address = DB::selectOne('select atp_id from addresses where id = ?', [$addressId])) {
            return $address;
        }
        throw new Exception("Address '$addressId' not found", 404);
    }
}
