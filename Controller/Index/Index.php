<?php
/**
 * A Magento 2 module named Davix/AutoCoupon
 * Copyright (C) 2020 Davix
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

namespace Davix\AutoCoupon\Controller\Index;

use Davix\AutoCoupon\Model\Config;
use Davix\AutoCoupon\Model\StickyCouponManager;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Index extends Action implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private readonly StoreManagerInterface $storeManager,
        private readonly Config $config,
        private readonly CheckoutSession $checkoutSession,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly StickyCouponManager $stickyCouponManager
    ) {
        parent::__construct($context);
    }

    /**
     * Apply (or clear) a coupon code from the querystring, then redirect.
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        if (!$this->config->isEnabled()) {
            return $this->_redirect('/');
        }

        $couponCode = (string)$this->getRequest()->getParam('code');
        $redirectUrl = (string)$this->getRequest()->getParam('redirect_url');

        $quote = $this->checkoutSession->getQuote();

        $customerId = $quote->getCustomerId() ? (int)$quote->getCustomerId() : null;

        if ($couponCode !== '') {
            $quote->setCouponCode($couponCode)->collectTotals();
            $this->cartRepository->save($quote);

            if ($quote->getCouponCode()) {
                $this->messageManager->addSuccessMessage($this->config->getSuccessMessage());

                if ($customerId !== null) {
                    $this->stickyCouponManager->save($customerId, $couponCode);
                }
            } else {
                $this->messageManager->addErrorMessage($this->config->getErrorMessage());
            }
        } else {
            $quote->setCouponCode('')->collectTotals();
            $this->cartRepository->save($quote);

            if ($customerId !== null) {
                $this->stickyCouponManager->remove($customerId);
            }
        }

        if ($redirectUrl !== '' && $this->isSafeRedirectUrl($redirectUrl)) {
            return $this->_redirect($redirectUrl);
        }

        return $this->_redirect('/');
    }

    /**
     * Only allow redirecting back to this store's own host. Without this check, `redirect_url`
     * is an open redirect: a link using this site's real domain could bounce visitors to any
     * external URL, which is exactly the pattern used in phishing campaigns.
     */
    private function isSafeRedirectUrl(string $url): bool
    {
        $url = ltrim($url);

        // Some browsers treat a leading "//" or "/\" as protocol-relative, i.e. an external host.
        if (str_starts_with($url, '//') || str_starts_with($url, '/\\')) {
            return false;
        }

        $parsedUrl = parse_url($url);
        if ($parsedUrl === false) {
            return false;
        }

        if (!isset($parsedUrl['host'])) {
            return true;
        }

        $storeHost = parse_url((string)$this->storeManager->getStore()->getBaseUrl(), PHP_URL_HOST);

        return $storeHost !== null && strcasecmp($parsedUrl['host'], $storeHost) === 0;
    }
}
