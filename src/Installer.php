<?php

declare(strict_types=1);

/**
 * Derafu: Site - Base for Derafu’s Websites.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\Site;

use Composer\Script\Event;
use Derafu\Foundation\Installer as DerafuInstaller;

/**
 * Installer class to handle install and update tasks.
 */
class Installer
{
    /**
     * Tasks to execute during composer install.
     *
     * @param Event $event The composer event
     * @return void
     */
    public static function install(Event $event): void
    {
        // Copies files to their destinations during composer install.
        DerafuInstaller::copyFiles($event);
    }

    /**
     * Tasks to execute during composer update.
     *
     * @param Event $event The composer event
     * @return void
     */
    public static function update(Event $event): void
    {
        // Copies files to their destinations during composer update.
        self::install($event);
    }
}
