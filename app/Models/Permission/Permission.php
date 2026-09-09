<?php

namespace App\Models\Permission;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Spatie\Permission\Models\Permission as SpatiePermission;

#[Fillable(['name', 'guard_name', 'label', 'category'])]
class Permission extends SpatiePermission {}
