<?php

namespace App\Models\Hero;

use App\Models\Concerns\HasMedia;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['badge', 'title', 'description', 'button_text', 'button_url'])]
class Hero extends Model
{
    use HasMedia;
}
