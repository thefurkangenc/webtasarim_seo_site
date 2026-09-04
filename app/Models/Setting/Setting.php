<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['group', 'key', 'value'])]
class Setting extends Model
{
    //
}
