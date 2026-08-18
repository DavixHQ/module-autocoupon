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

namespace Davix\AutoCoupon\Plugin;

use Davix\AutoCoupon\Model\Config;
use Davix\AutoCoupon\Model\StickyCouponManager;
use Magento\Checkout\Controller\Cart\CouponPost;
use Magento\Checkout\Model\Session as CheckoutSession;

class ClearStickyCouponOnCancelPlugin
{
    public function __construct(
        private readonly Config $config,
        private readonly CheckoutSession $checkoutSession,
        private readonly StickyCouponManager $stickyCouponManager
    ) {
    }

    /**
     * When a customer clicks "Cancel Coupon" on the cart page, forget it as their sticky
     * coupon too - otherwise Observer\Frontend\ApplyCoupon would just bring it back on their
     * next login, which isn't what a customer removing it on purpose would expect.
     *
     * @param mixed $result
     * @return mixed
     */
    public function afterExecute(CouponPost $subject, $result)
    {
        if (!$this->config->isEnabled()) {
            return $result;
        }

        if ((int)$subject->getRequest()->getParam('remove') !== 1) {
            return $result;
        }

        $quote = $this->checkoutSession->getQuote();
        if ($quote->getCouponCode()) {
            return $result;
        }

        $customerId = $quote->getCustomerId();
        if ($customerId) {
            $this->stickyCouponManager->remove((int)$customerId);
        }

        return $result;
    }
}
