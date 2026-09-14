@extends('admin.layout.app')
@section('admin.title', $user ? 'Kullanıcıyı Düzenle' : 'Yeni Kullanıcı')

@section('content')
    <div class="mb-[25px] md:flex items-center justify-between">
        <h5 class="!mb-0">{{ $user ? 'Kullanıcıyı Düzenle' : 'Yeni Kullanıcı' }}</h5>
        <ol class="breadcrumb mt-[12px] md:mt-0">
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-block relative ltr:pl-[22px] rtl:pr-[22px] transition-all hover:text-primary-500">
                    <i class="material-symbols-outlined absolute ltr:left-0 rtl:right-0 !text-lg -mt-px text-primary-500 top-1/2 -translate-y-1/2">home</i>
                    Dashboard
                </a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                <a href="{{ route('admin.user.index') }}" class="transition-all hover:text-primary-500">Kullanıcılar</a>
            </li>
            <li class="breadcrumb-item inline-block relative text-sm mx-[11px] ltr:first:ml-0 rtl:first:mr-0 ltr:last:mr-0 rtl:last:ml-0">
                {{ $user ? 'Düzenle' : 'Yeni' }}
            </li>
        </ol>
    </div>

    <form id="user-form" data-id="{{ $user?->id }}">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-[25px]">
            <div class="lg:col-span-2">
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Hesap Bilgileri</h5>
                        </div>
                    </div>

                    <div class="trezo-card-content">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-[20px]">
                            <x-admin::form.input name="name" help="user.name" label="Ad Soyad" required
                                :value="$user?->name" placeholder="Örn. Ayşe Yılmaz" />

                            <x-admin::form.input name="email" type="email" help="user.email" label="E-posta" required
                                :value="$user?->email" placeholder="ornek@sirket.com" autocomplete="off" />
                        </div>

                        <x-admin::form.phone name="phone" country-name="country_id" help="user.phone"
                            :phone="$user?->phone" :country-id="$user?->country_id" :countries="$countries"
                            hint="Numara yazıldıkça seçilen ülkenin formatına çevrilir. Baştaki 0 Türkiye için otomatik düşer." />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-[20px]">
                            <x-admin::form.input name="password" type="password" help="user.password"
                                :label="$user ? 'Yeni Şifre' : 'Şifre'" :required="! $user"
                                autocomplete="new-password"
                                :placeholder="$user ? 'Değişmeyecekse boş bırakın' : 'En az 8 karakter, harf ve rakam'" />

                            <x-admin::form.input name="password_confirmation" type="password"
                                :label="$user ? 'Yeni Şifre (tekrar)' : 'Şifre (tekrar)'" :required="! $user"
                                autocomplete="new-password" />
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Yetki ve Durum</h5>
                        </div>
                    </div>

                    <div class="trezo-card-content">
                        @if ($lockRole)
                            <div class="mb-[20px] md:mb-[25px]">
                                <x-admin::form.label help="user.role_id">Rol</x-admin::form.label>
                                <span class="h-[42px] rounded-md text-sm text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-gray-50 dark:bg-[#15203c] px-[14px] flex items-center">
                                    {{ $user->roleLabel() }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">
                                    {{ $user->isSuperAdmin()
                                        ? 'Süper yönetici rolü panelden değiştirilemez.'
                                        : 'Kendi rolünüzü buradan değiştiremezsiniz.' }}
                                </span>
                            </div>
                        @else
                            <x-admin::form.select name="role_id" help="user.role_id" label="Rol" required
                                :options="$roles" :value="$roleId" placeholder="Rol seçin" />
                        @endif

                        @if ($lockStatus)
                            <div class="mb-[20px] md:mb-[25px]">
                                <x-admin::form.label help="user.is_active">Hesap durumu</x-admin::form.label>
                                <span class="inline-block py-[3px] px-[10px] rounded-sm text-xs {{ $user->is_active ? 'bg-success-100 dark:bg-[#15203c] text-success-600 dark:text-success-500' : 'bg-danger-100 dark:bg-[#15203c] text-danger-500' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                                <span class="text-gray-500 dark:text-gray-400 text-xs mt-[6px] block">
                                    {{ $user->isSuperAdmin()
                                        ? 'Süper yönetici hesabı pasifleştirilemez.'
                                        : 'Kendi hesabınızı pasifleştiremezsiniz.' }}
                                </span>
                            </div>
                        @else
                            <x-admin::form.switch name="is_active" help="user.is_active" label="Hesap aktif"
                                :checked="old('is_active', $user?->is_active ?? true)"
                                hint="Pasif hesap panele giriş yapamaz." />
                        @endif

                        @if ($user)
                            <div class="mb-[20px] md:mb-[25px] last:mb-0">
                                <span class="mb-[8px] text-sm text-black dark:text-white font-medium block">Son giriş</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $user->last_login_at?->translatedFormat('d F Y H:i') ?? 'Henüz giriş yapmadı' }}
                                </span>
                            </div>
                        @endif

                        <div class="trezo-card-footer flex items-center justify-end gap-[12px] -mx-[20px] md:-mx-[25px] px-[20px] md:px-[25px] pt-[20px] md:pt-[25px] mt-[5px] border-t border-gray-100 dark:border-[#172036]">
                            <a href="{{ route('admin.user.index') }}"
                                class="inline-block py-[10px] px-[25px] text-black dark:text-white transition-all rounded-md border border-gray-200 dark:border-[#172036] hover:bg-gray-50 dark:hover:bg-[#15203c]">
                                Vazgeç
                            </a>
                            <button type="submit"
                                class="inline-block py-[10px] px-[25px] bg-primary-500 text-white transition-all hover:bg-primary-400 rounded-md border border-primary-500 hover:border-primary-400">
                                {{ $user ? 'Güncelle' : 'Kaydet' }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                    <div class="trezo-card-header mb-[20px] md:mb-[25px]">
                        <div class="trezo-card-title">
                            <h5 class="!mb-0">Profil Fotoğrafı</h5>
                        </div>
                    </div>
                    <div class="trezo-card-content">
                        <x-admin::form.image name="avatar_media_id" label="" preset="user.avatar"
                            help="user.avatar_media_id"
                            :media="$user?->getFirstMedia('avatar')" wrapper=""
                            hint="Kare kırpılır. Boş bırakırsanız baş harfler gösterilir." />
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('admin.scripts')
    <script type="module" src="{{ asset('admin/assets/js/pages/user/form.js') }}"></script>
@endpush
