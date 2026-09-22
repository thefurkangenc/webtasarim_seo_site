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
     * Tekilleştirme yok: adrese kaç istek gelirse o kadar job kuyruğa girer
     * ve sırayla işlenir — art arda birden çok yazı üretmek bilinçli olarak
     * mümkündür.
     */
    public function generate(Request $request, string $secret): JsonResponse
    {
        abort_if(blank(config('auto-blog.secret')) || ! hash_equals((string) config('auto-blog.secret'), $secret), 404);

        GenerateAutoBlogJob::dispatch(array_filter(
            $request->only(['keywords', 'title', 'notes']),
            fn ($value) => is_string($value) && trim($value) !== '',
        ));

        return $this->success('Yazı üretimi kuyruğa alındı.');
    }
}
