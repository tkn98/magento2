<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Model\Config\Backend\Enabled\SubscriptionHandler;
use Magento\Config\App\Config\Type\System;

/**
 * Provider of subscription status.
 */
class SubscriptionStatusProvider
{
    /**
     * Represents an enabled subscription state.
     */
    const ENABLED = "Enabled";

    /**
     * Represents a failed subscription state.
     */
    const FAILED = "Failed";

    /**
     * Represents a pending subscription state.
     */
    const PENDING = "Pending";

    /**
     * Represents a disabled subscription state.
     */
    const DISABLED = "Disabled";

    /**
     * @var System
     */
    private $systemConfig;

    /**
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @param System $systemConfig
     * @param AnalyticsToken $analyticsToken
     * @param FlagManager $flagManager
     */
    public function __construct(
        System $systemConfig,
        AnalyticsToken $analyticsToken,
        FlagManager $flagManager
    ) {
        $this->systemConfig = $systemConfig;
        $this->analyticsToken = $analyticsToken;
        $this->flagManager = $flagManager;
    }

    /**
     * Retrieve subscription status to Magento BI Advanced Reporting.
     *
     * Statuses:
     * Enabled - if subscription is enabled and MA token was received;
     * Pending - if subscription is enabled and MA token was not received;
     * Disabled - if subscription is not enabled.
     * Failed - if subscription is enabled and token was not received after attempts ended.
     *
     * @return string
     */
    public function getStatus()
    {
        $isSubscriptionEnabledInConfig = $this->systemConfig->get('default/analytics/subscription/enabled');
        if ($isSubscriptionEnabledInConfig) {
            return $this->getStatusForEnabledSubscription();
        }

        return $this->getStatusForDisabledSubscription();
    }

    /**
     * Retrieve status for subscription that enabled in config.
     *
     * @return string
     */
    public function getStatusForEnabledSubscription()
    {
        $status = static::ENABLED;
        if (!$this->analyticsToken->isTokenExist()) {
            $status = static::PENDING;
            if ($this->flagManager->getFlagData(SubscriptionHandler::ATTEMPTS_REVERSE_COUNTER_FLAG_CODE) === null) {
                $status = static::FAILED;
            }
        }

        return $status;
    }

    /**
     * Retrieve status for subscription that disabled in config.
     *
     * @return string
     */
    public function getStatusForDisabledSubscription()
    {
        return static::DISABLED;
    }
}
