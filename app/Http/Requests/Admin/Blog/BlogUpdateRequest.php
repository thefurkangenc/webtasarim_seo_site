<?php

namespace App\Http\Requests\Admin\Blog;

/**
 * Kurallar ekleme ile aynı; slug benzersizlik kuralı route'taki yazıyı
 * kendiliğinden hariç tutar.
 */
class BlogUpdateRequest extends BlogCreateRequest {}
