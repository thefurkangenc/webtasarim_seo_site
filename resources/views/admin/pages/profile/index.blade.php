@extends('admin.layout.app')
@section('admin.title', 'Profilim')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">Profilim</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                Profilim
            </li>
        </ol>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-[25px]">

        {{-- Sol: özet kartı --}}
        <div>
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md text-center">
                <div class="trezo-card-content">
                    @if ($avatar)
                        <img src="{{ $avatar->url('thumb') }}" alt="{{ $user->name }}"
                            class="w-[96px] h-[96px] rounded-full object-cover mx-auto border-[3px] border-primary-200">
                    @else
                        {{-- Şablondan kalan sabit admin.png kullanılmıyor: görsel
                             yoksa baş harfler basılır, böylece boş bir çerçeve
                             ya da yanlış bir yüz görünmez. --}}
                        <span class="w-[96px] h-[96px] rounded-full mx-auto flex items-center justify-center bg-primary-50 dark:bg-[#15203c] text-primary-500 text-[30px] font-bold border-[3px] border-primary-200">
                            {{ $user->initials() }}
                        </span>
                    @endif

                    <h6 class="!mb-[2px] mt-[14px] text-black dark:text-white">{{ $user->name }}</h6>
                    <span class="block text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</span>

                    <div class="flex items-center justify-center gap-[6px] flex-wrap mt-[12px]">
                        @foreach ($roles as $role)
                            <span class="py-[3px] px-[10px] rounded-sm text-[11px] font-medium bg-primary-50 dark:bg-[#15203c] text-primary-500">
                                {{ $role }}
                            </span>
                        @endforeach
                    </div>

                    <div class="mt-[18px] pt-[18px] border-t border-gray-100 dark:border-[#172036] text-left">
                        <div class="flex items-center justify-between gap-[10px] mb-[10px]">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Yetki sayısı</span>
                            <span class="text-xs font-medium text-black dark:text-white">
                                {{ $permissionCount === null ? 'Tüm yetkiler' : $permissionCount }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-[10px]">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Hesap oluşturma</span>
                            <span class="text-xs font-medium text-black dark:text-white">
                                {{ $user->created_at?->translatedFormat('d F Y') ?? '—' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[18px] rounded-md flex items-start gap-[10px]">
                <i class="material-symbols-outlined !text-[19px] text-info-500 shrink-0">info</i>
                <span class="text-xs text-gray-500 dark:text-gray-400 leading-[1.7]">
                    Burada yalnızca kendi hesabınızı düzenlersiniz. Rolünüzü ve yetkilerinizi
                    buradan değiştiremezsiniz — onlar
                    @can('role.index')
                        <a href="{{ route('admin.role.index') }}" class="text-primary-500 hover:underline">Roller ve İzinler</a>
                    @else
                        Roller ve İzinler
                    @endcan
                    ekranından bir yöneticinin atadığı değerlerdir.
                </span>
            </div>
        </div>

        {{-- Sağ: formlar --}}
        <div class="lg:col-span-2">
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">Hesap Bilgileri</h5>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Adınız ve fotoğrafınız panelin sağ üst köşesinde görünür.
                        </span>
                    </div>
                </div>

                <div class="trezo-card-content">
                    <form id="profile-form">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-[20px]">
                            <x-admin::form.input name="name" label="Ad Soyad" required :value="$user->name" />
                            <x-admin::form.input name="email" type="email" label="E-posta" required :value="$user->email" />
                        </div>

                        <x-admin::form.image name="avatar_media_id" label="Profil Fotoğrafı" preset="user.avatar"
                            :media="$avatar" hint="Kare kırpılır. Boş bırakırsanız baş harfleriniz gösterilir." />

                        <x-admin::form.actions submit="Bilgileri Kaydet" />
                    </form>
                </div>
            </div>

            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">Şifre Değiştir</h5>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            En az 8 karakter, harf ve rakam içermeli.
                        </span>
                    </div>
                </div>

                <div class="trezo-card-content">
                    <form id="password-form">
                        <x-admin::form.input name="current_password" type="password" label="Mevcut Şifre" required
                            autocomplete="current-password" />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-[20px]">
                            <x-admin::form.input name="password" type="password" label="Yeni Şifre" required
                                autocomplete="new-password" />
                            <x-admin::form.input name="password_confirmation" type="password" label="Yeni Şifre (tekrar)"
                                required autocomplete="new-password" />
                        </div>

                        <x-admin::form.actions submit="Şifreyi Değiştir" />
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/profile/index.js') }}"></script>
@endpush
