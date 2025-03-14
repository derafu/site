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

/**
 * Represents a teapot.
 *
 * It's a example class that returns a string when converted to a string.
 */
final class Teapot
{
    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return "I'm a teapot";
    }
}
