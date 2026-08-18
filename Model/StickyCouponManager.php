<?php
/**
 * Davix Auto Coupon
 * Copyright (C) 2020  Davix
 *
 * This file is part of Davix/AutoCoupon.
 *
 * Davix/AutoCoupon is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Davix\AutoCoupon\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * Remembers the last coupon code a customer successfully applied, so it can be
 * reapplied automatically on their next cart/login (see Observer\Frontend\ApplyCoupon).
 */
class StickyCouponManager
{
    private const TABLE = 'davix_autocoupon_sticky_coupon';

    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    public function getCouponCode(int $customerId): ?string
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName(self::TABLE), ['coupon_code'])
            ->where('customer_id = ?', $customerId);

        $couponCode = $connection->fetchOne($select);

        return $couponCode !== false ? (string)$couponCode : null;
    }

    public function save(int $customerId, string $couponCode): void
    {
        $this->resourceConnection->getConnection()->insertOnDuplicate(
            $this->resourceConnection->getTableName(self::TABLE),
            ['customer_id' => $customerId, 'coupon_code' => $couponCode],
            ['coupon_code']
        );
    }

    public function remove(int $customerId): void
    {
        $this->resourceConnection->getConnection()->delete(
            $this->resourceConnection->getTableName(self::TABLE),
            ['customer_id = ?' => $customerId]
        );
    }
}
