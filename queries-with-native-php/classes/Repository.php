<?php


class Repository
{
    private mixed $connection;
    
    public function __construct(string $host, string $port, string $db, string $user, string $password)
    {
        $this->connection = pg_connect("host=$host port=$port dbname=$db user=$user password=$password");
    }
    
    // 1
    public function getAddressObjectTypeValues(string $addressObjectTypeCode): array
    {
        return pg_fetch_all(
            pg_query_params(
                $this->connection,
                'select aotv.*
            from address_object_type_values aotv
                     inner join address_object_types aot on aot.id = aotv.aot_id
            where aot.code = $1 order by aotv.value',
                [$addressObjectTypeCode]
            )
        );
    }
    
    // 2
    public function getAvailableAddressesForPickupPoint(): array
    {
        return pg_fetch_all(
            pg_query_params(
                $this->connection,
                'select distinct a.*
            from addresses a
                     inner join address_types at on at.id = a.atp_id
                     inner join delivery_type_available_address_types dtaat on at.id = dtaat.atp_id
                     inner join delivery_types dt on dt.id = dtaat.dty_id
            where dt.type = $1 order by a.full_address',
                ['PICKUP_POINT']
            )
        );
    }
    
    // 3
    public function createAddress(?int $addressId, string $addressTypeCode, array $values): int
    {
        $addressObjectTypes = [];
        $dbAddressObjectTypes = pg_query_params(
            $this->connection,
            'select aot.id, aot.code, is_free_text from address_object_types aot
            inner join address_types at on at.id = aot.atp_id
                where at.code = $1 and aot.code = any ($2)',
            [$addressTypeCode, '{' . implode(', ', array_keys($values)) . '}']
        );
        while ($addressObjectType = pg_fetch_object($dbAddressObjectTypes)) {
            $addressObjectTypes[$addressObjectType->code] = [
                'id' => (int)$addressObjectType->id,
                'is_free_text' => $addressObjectType->is_free_text === 't'
            ];
        }
        
        pg_query('begin');
        
        if ($addressId) {
            if (! pg_query_params(
                    $this->connection,
                    'update addresses a
                set atp_id = (select atp.id from address_types atp where upper(atp.code) = upper($1))
                where a.id = $2',
                    [$addressTypeCode, $addressId]
                ) ||
                ! pg_query_params($this->connection, 'delete
                from address_objects
                where addr_id = $1', [$addressId])) {
                pg_query('rollback');
                throw new Exception("Address updating error");
            }
        } else {
            $address = pg_fetch_object(
                pg_query_params(
                    $this->connection,
                    'insert into addresses (id, atp_id)
            values (nextval(\'addr_seq\'), (select atp.id from address_types atp where upper(atp.code) = upper($1)))
            returning id',
                    [$addressTypeCode]
                )
            );
            $addressId = (int)$address->id;
        }
        
        foreach ($values as $addressObjectTypeCode => $addressObjectTypeValue) {
            if (! empty($addressObjectTypeValue)) {
                if ($addressObjectTypes[$addressObjectTypeCode]['is_free_text']) {
                    $res = pg_query_params(
                        $this->connection,
                        'insert into address_objects (addr_id, aot_id, value)
                values ($1, $2, $3)',
                        [
                            $addressId,
                            $addressObjectTypes[$addressObjectTypeCode]['id'],
                            $addressObjectTypeValue
                        ]
                    );
                } else {
                    if ($type = pg_fetch_object(
                        pg_query_params(
                            $this->connection,
                            'select aov.id
                             from address_types atp
                                      join address_object_types aot on aot.atp_id = atp.id and upper(aot.code) = upper($1)
                                      join address_object_type_values aov on aov.aot_id = aot.id and upper(aov.value) like \'%\' || upper($2) || \'%\'
                             where upper(atp.code) = upper($3)',
                            [
                                $addressObjectTypeCode,
                                $addressObjectTypeValue,
                                $addressTypeCode
                            ]
                        )
                    )) {
                        $res = pg_query_params(
                            $this->connection,
                            'insert into address_objects (addr_id, aot_id, aov_id)
                    values ($1, $2, $3)',
                            [
                                $addressId,
                                $addressObjectTypes[$addressObjectTypeCode]['id'],
                                (int)$type->id
                            ]
                        );
                    } else {
                        pg_query('rollback');
                        throw new Exception("Address object type value '$addressObjectTypeValue' not found", 404);
                    }
                }
                
                if ($res === false) {
                    pg_query('rollback');
                    throw new Exception("Address object type value inserting error: " . pg_last_error($this->connection));
                }
            }
        }
        $this->setFullAddress($addressId);
        
        pg_query('commit');
        
        return $addressId;
    }
    
    // 4
    public function getAddressDisplayValues(int $addressId): array
    {
        if ($dbAddressObjects = pg_query_params(
            $this->connection,
            'select aot.code as code, aot.name as type, (coalesce(aotv.value, ao.value)) as value
            from address_objects ao
                     inner join address_object_types aot on aot.id = ao.aot_id
                     left join address_object_type_values aotv on aotv.id = ao.aov_id
            where ao.addr_id = $1
            order by aot.sequence_number',
            [$addressId]
        )) {
            if (count($result = pg_fetch_all($dbAddressObjects)) === 0) {
                throw new Exception("Address '$addressId' not found", 404);
            }
            return $result;
        }
        
        throw new Exception("Query error: " . pg_last_error($this->connection));
    }
    
    // 5
    public function setFullAddress(int $addressId)
    {
        if ($dbAddressObjects = pg_query_params(
            $this->connection,
            'select aotv.value as enum_value, ao.value as text_value
            from address_objects ao
                    inner join address_object_types aot on aot.id = ao.aot_id
                    left join address_object_type_values aotv on aotv.id = ao.aov_id
            where ao.addr_id = $1
            order by aot.sequence_number',
            [$addressId]
        )) {
            $values = [];
            foreach (pg_fetch_all($dbAddressObjects) as $addressObject) {
                $values[] = $addressObject['enum_value'] ?? $addressObject['text_value'];
            }
            
            if (pg_query_params(
                    $this->connection,
                    'update addresses set full_address = $1 where id = $2',
                    [implode(', ', array_filter($values)), $addressId]
                ) === false) {
                throw new Exception("Address updating error: " . pg_last_error($this->connection));
            }
        }
    }
    
    // 6
    public function getDeliveryFee(string $deliveryTypeCode, ?string $addressTypeCode = null, ?float $orderAmount = null, ?float $orderWeight = null): float
    {
        if ($dbDeliveryFee = pg_query_params(
            $this->connection,
            'select dfc.delivery_fee
            from delivery_fee_configuration dfc
                     inner join delivery_types dt on dt.id = dfc.dty_id
                     left join address_types at on at.id = dfc.atp_id
            where dt.code = $1
              and (dfc.atp_id is null or at.code = $2)
              and (
                    dfc.total_product_weight_from is null or dfc.total_product_weight_from < $3
                    and dfc.total_product_weight_to is null or dfc.total_product_weight_to >= $3
                )
              and (
                    dfc.order_total_amount_from is null or dfc.order_total_amount_from < $4
                    and dfc.order_total_amount_to is null or dfc.order_total_amount_to >= $4
                ) order by dfc.delivery_fee limit 1',
            [$deliveryTypeCode, $addressTypeCode, $orderWeight, $orderAmount]
        )) {
            if (($object = pg_fetch_object($dbDeliveryFee)) === false) {
                throw new Exception("Delivery fee for your conditions not found", 404);
            }
            return (float)$object->delivery_fee;
        }
        
        throw new Exception("Query error: " . pg_last_error($this->connection));
    }
    
    // 7
    public function changeDpdDeliveryForRiga(): bool
    {
        if (! ($res = pg_query_params(
                $this->connection,
                'select id from address_object_type_values where value = $1',
                ['Riga']
            )) || ! ($addressObjectTypeValue = pg_fetch_object(
                $res
            ))) {
            if (! ($addressObjectTypeValue = pg_fetch_object(
                pg_query_params(
                    $this->connection,
                    'insert into address_object_type_values (id, aot_id, value)
                values (nextval(\'aov_seq\'),
                        (select aot.id
                         from address_object_types aot
                                  inner join address_types at on at.id = aot.atp_id
                         where at.code = $1
                           and aot.code = $2),
                        $3) returning id',
                    ['LV', 'CITY', 'Riga']
                )
            ))) {
                throw new Exception("Adding Riga error: " . pg_last_error($this->connection));
            }
        }
        
        $addressObjectTypeValueId = (int)$addressObjectTypeValue->id;
        
        if (pg_query_params(
            $this->connection,
            'insert into delivery_fee_configuration (id, dty_id, dft_id, aov_id, delivery_fee)
            values (nextval(\'dfc_seq\'), (select id from delivery_types where code = $1),
                    (select id from delivery_fee_types where code = $2),
                    $3,
                    greatest((select delivery_fee
                     from delivery_fee_configuration
                     where dty_id = (select id from delivery_types where code = $1)
                       and dft_id = (select id from delivery_fee_types where code = $2)
                       and aov_id is null
                       and atp_id is null
                     limit 1) - 5, 0))',
            ['DPD', 'BASE', $addressObjectTypeValueId]
        )) {
            return true;
        }
        
        throw new Exception("Delivery configuration updating error: " . pg_last_error($this->connection));
    }
    
    // 8
    public function getDeliveryFeeForSaturday(string $deliveryTypeCode, ?string $addressTypeCode = null): float
    {
        if ($dbDeliveryFee = pg_query_params(
            $this->connection,
            'select (case when extract(isodow from now()) = 6 then delivery_fee + 3 else delivery_fee end) as delivery_fee
            from delivery_fee_configuration dfc
                     inner join delivery_types dt on dt.id = dfc.dty_id
                     left join address_types at on at.id = dfc.atp_id
            where dt.code = $1
              and (dfc.atp_id is null or at.code = $2)
              order by delivery_fee limit 1',
            [$deliveryTypeCode, $addressTypeCode]
        )) {
            if (($object = pg_fetch_object($dbDeliveryFee)) === false) {
                throw new Exception("Delivery fee for your conditions not found", 404);
            }
            return (float)$object->delivery_fee;
        }
        
        throw new Exception("Query error: " . pg_last_error($this->connection));
    }
    
    // 9
    public function getDeliveryPointsCountForOmniva(): array
    {
        if ($dbPointsCount = pg_query_params(
            $this->connection,
            'select aotv.value as city, count(pp.id) as count
            from address_object_type_values aotv
                     inner join address_object_types aot on aot.id = aotv.aot_id
                     inner join address_objects ao on aotv.id = ao.aov_id
                     inner join pickup_points pp on ao.addr_id = pp.addr_id
                     inner join delivery_types dt on pp.dty_id = dt.id
            where dt.code = $1
              and aot.code = $2
            group by aotv.value
            order by aotv.value',
            ['OMNIVA_PICKUP', 'CITY']
        )) {
            return pg_fetch_all($dbPointsCount);
        }
        
        throw new Exception("Query error: " . pg_last_error($this->connection));
    }
    
    // 10
    public function createDeliveryConfigurationsForDpdLithuania(): bool
    {
        if (pg_query_params(
                $this->connection,
                'insert into delivery_fee_configuration(id, dty_id, dft_id, atp_id, total_product_weight_to, delivery_fee)
            values (nextval(\'dfc_seq\'), (select id from delivery_types where code = $1),
                    (select id from delivery_fee_types where code = $2),
                    (select id from address_types where code = $3), $4, $5)',
                ['DPD', 'BASE', 'LT', 10, 10]
            ) && pg_query_params(
                $this->connection,
                'insert into delivery_fee_configuration(id, dty_id, dft_id, atp_id, total_product_weight_from, delivery_fee)
            values (nextval(\'dfc_seq\'), (select id from delivery_types where code = $1),
                    (select id from delivery_fee_types where code = $2),
                    (select id from address_types where code = $3), $4, $5)',
                ['DPD', 'BASE', 'LT', 10, 10]
            )) {
            return true;
        }
        
        throw new Exception("Query error: " . pg_last_error($this->connection));
    }
    
    // 11
    public function normalizeContactPhones(): bool
    {
        if (pg_query(
            $this->connection,
            'update contacts
            set phone_number = replace(regexp_replace(phone_number, \'^[0+ ]{0,2}371\', \'\'), \' \', \'\')',
        )) {
            return true;
        }
        
        throw new Exception("Query error: " . pg_last_error($this->connection));
    }
    
    // 12
    public function deleteDuplicateContactPhones(): bool
    {
        if (pg_query(
            $this->connection,
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
        
        throw new Exception("Query error: " . pg_last_error($this->connection));
    }
    
    // 13
    public function getOrderNumber(): int
    {
        pg_query($this->connection, 'create sequence if not exists order_seq');
        $value = pg_fetch_object(pg_query('select nextval(\'order_seq\') as value'));
        return (int)$value->value;
    }
    
    public function getAddressTypes(): array
    {
        return pg_fetch_all(pg_query($this->connection, 'select * from address_types'));
    }
    
    public function getAddressObjectTypes(): array
    {
        return pg_fetch_all(pg_query($this->connection, 'select * from address_object_types'));
    }
    
    public function getDeliveryTypes(): array
    {
        return pg_fetch_all(pg_query($this->connection, 'select * from delivery_types'));
    }
    
    public function getAddress(int $addressId): array
    {
        if ($address = pg_fetch_assoc(pg_query_params($this->connection, 'select atp_id from addresses where id = $1', [$addressId]))) {
            return $address;
        }
        throw new Exception("Address '$addressId' not found", 404);
    }
}
