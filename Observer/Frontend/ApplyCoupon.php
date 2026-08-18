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

namespace Davix\AutoCoupon\Observer\Frontend;

use Davix\AutoCoupon\Model\Config;
use Davix\AutoCoupon\Model\StickyCouponManager;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class ApplyCoupon implements ObserverInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly CheckoutSession $checkoutSession,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly StickyCouponManager $stickyCouponManager
    ) {
    }

    /**
     * If the cart already has a coupon, re-collect totals so the discount stays applied after
     * events (adding an item, logging in) that can otherwise leave it uncollected against the
     * cart's current contents. Otherwise, if this is a logged-in customer with no coupon on
     * their cart, reapply whichever coupon they last successfully applied.
     *
     * Relies on module.xml sequencing Davix_AutoCoupon after Magento_Checkout, so on
     * customer_login this runs after Magento\Checkout\Observer\LoadCustomerQuoteObserver has
     * already merged the guest cart into the customer's real quote.
     */
    public function execute(Observer $observer): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $quote = $this->checkoutSession->getQuote();
        $couponCode = (string)$quote->getCouponCode();

        if ($couponCode !== '') {
            $quote->setCouponCode($couponCode)->collectTotals();
            $this->cartRepository->save($quote);
            return;
        }

        $customerId = $quote->getCustomerId();
        if (!$customerId) {
            return;
        }

        $stickyCouponCode = $this->stickyCouponManager->getCouponCode((int)$customerId);
        if ($stickyCouponCode === null) {
            return;
        }

        $quote->setCouponCode($stickyCouponCode)->collectTotals();
        $this->cartRepository->save($quote);
    }
}
