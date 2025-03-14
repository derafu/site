<?php

declare(strict_types=1);

/**
 * Derafu: Site - Base for Derafu’s Websites.
 *
 * Copyright (c) 2025 Esteban De La Fuente Rubio / Derafu <https://www.derafu.org>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Derafu\TestsSite;

use Derafu\Site\Teapot;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test for the Teapot class.
 *
 * It's a example test that covers the Teapot class.
 */
#[CoversClass(Teapot::class)]
final class TeapotTest extends TestCase
{
    public function testDummyAssertTrue(): void
    {
        $this->assertSame("I'm a teapot", (string)(new Teapot()));
    }
}
