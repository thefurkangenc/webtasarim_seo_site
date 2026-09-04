<?php

namespace App\Models\Contact;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'phone', 'message', 'ip_address', 'user_agent'])]
class ContactSubmission extends Model
{
    //
}
