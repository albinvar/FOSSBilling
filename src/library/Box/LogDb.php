<?php

/**
 * Copyright 2022-2023 FOSSBilling
 * Copyright 2011-2021 BoxBilling, Inc.
 * SPDX-License-Identifier: Apache-2.0
 *
 * @copyright FOSSBilling (https://www.fossbilling.org)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 */

class Box_LogDb
{
    private array $ignoredChannels = ['billing', 'routing'];
    /**
     * Class constructor
     *
     * @param object $service - module service class object
     */
    public function __construct(protected $service)
    {
    }

    /**
     * Write a message to the log.
     *
     *
     * @return void
     */
    public function write(array $event, string $channel = 'application'): void
    {
        // TOOD: Temporary! Redo logging stuff in more depth for a major release.
        if (in_array($channel, $this->ignoredChannels)) {
            return;
        }

        try {
            if (method_exists($this->service, 'logEvent')) {
                $this->service->logEvent($event);
            }
        } catch (Exception $e) {
            error_log($e);
        }
    }
}
