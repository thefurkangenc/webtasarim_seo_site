<?php

namespace App\Models\Contact;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'phone', 'message', 'ip_address', 'user_agent'])]
class ContactSubmission extends Model
{
    use LogsActivity;

    /** Log modül anahtarı: bu model kendi adıyla değil 'contact' altında toplanır. */
    public function activityLogName(): string
    {
        return 'contact';
    }

    //
}
