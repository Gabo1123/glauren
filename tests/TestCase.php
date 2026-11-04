<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \App\Modules\Chaquetas\Models\Setting::flushCache();   // cada prueba arranca sin ajustes en memoria
    }
}
