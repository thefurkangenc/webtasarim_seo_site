<?php

namespace App\Http\Controllers\AutoBlog;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateAutoBlogJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutoBlogController extends Controller
{
    use RespondsWithJson;

    /**
     * Cron bu adresi çağırır. Anahtar adresin içindedir; eşleşmezse ya da
     * config'te tanımlı değilse uç nokta hiç yokmuş gibi davranır.
     *
     * Üretim kuyrukta çalışır (`queue:work` şart); adres beklemeden döner.
     * Bekleyen bir üretim varken yenisi kuyruğa eklenmez.
     */
    public function generate(Request $request, string $secret): JsonResponse
    {
        abort_if(blank(config('auto-blog.secret')) || ! hash_equals((string) config('auto-blog.secret'), $secret), 404);

        GenerateAutoBlogJob::dispatch($request->only(['keywords', 'title', 'notes']));

        return $this->success('Yazı üretimi kuyruğa alındı.');
    }
}
