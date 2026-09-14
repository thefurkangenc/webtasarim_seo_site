<?php

namespace App\Http\Requests\Setup\Concerns;

use App\Services\Setup\SetupService;

trait AuthorizesSetup
{
    public function authorize(): bool
    {
        $setup = app(SetupService::class);

        return $setup->databaseReady() && ! $setup->isComplete();
    }
}
