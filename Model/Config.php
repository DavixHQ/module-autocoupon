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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_STATUS = 'ioauto_coupon/general/status';
    private const XML_PATH_MESSAGE = 'ioauto_coupon/general/message';
    private const XML_PATH_ERROR_MESSAGE = 'ioauto_coupon/general/error_message';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_STATUS, ScopeInterface::SCOPE_STORE);
    }

    public function getSuccessMessage(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_MESSAGE, ScopeInterface::SCOPE_STORE);
    }

    public function getErrorMessage(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_ERROR_MESSAGE, ScopeInterface::SCOPE_STORE);
    }
}
