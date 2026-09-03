@extends('admin.layout.guest')
@section('admin.title', 'Giriş Yap')

@section('content')
    <div class="bg-white dark:bg-[#0a0e19] py-[60px] md:py-[80px] lg:py-[135px]">
        <div class="mx-auto px-[12.5px] md:max-w-[720px] lg:max-w-[960px] xl:max-w-[1255px]">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-[25px] items-center">
                <div
                    class="xl:ltr:-mr-[25px] xl:rtl:-ml-[25px] 2xl:ltr:-mr-[45px] 2xl:rtl:-ml-[45px] rounded-[25px] order-2 lg:order-1">
                    <img src="{{ asset('admin/assets/images/sign-in.jpg') }}" alt="Giriş" class="rounded-[25px]">
                </div>

                <div
                    class="xl:ltr:pl-[90px] xl:rtl:pr-[90px] 2xl:ltr:pl-[120px] 2xl:rtl:pr-[120px] order-1 lg:order-2">
                    <img src="{{ asset('admin/assets/images/logo-big.svg') }}" alt="Logo"
                        class="inline-block dark:hidden">
                    <img src="{{ asset('admin/assets/images/white-logo-big.svg') }}" alt="Logo"
                        class="hidden dark:inline-block">

                    <div class="my-[17px] md:my-[25px]">
                        <h1 class="!font-semibold !text-[22px] md:!text-xl lg:!text-2xl !mb-[5px] md:!mb-[7px]">
                            Yönetim Paneli
                        </h1>
                        <p class="font-medium lg:text-md text-[#445164] dark:text-gray-400">
                            Devam etmek için hesabınıza giriş yapın
                        </p>
                    </div>

                    @if ($errors->any())
                        <div
                            class="bg-danger-100 dark:bg-[#ffffff14] text-danger-500 rounded-md py-[12px] px-[17px] mb-[20px]">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('admin.login.store') }}" method="POST">
                        @csrf

                        <div class="mb-[15px] relative">
                            <label class="mb-[10px] md:mb-[12px] text-black dark:text-white font-medium block">
                                E-posta Adresi
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="h-[55px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[17px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500"
                                placeholder="ornek@site.com">
                        </div>

                        <div class="mb-[15px] relative" id="passwordHideShow">
                            <label class="mb-[10px] md:mb-[12px] text-black dark:text-white font-medium block">
                                Parola
                            </label>
                            <input type="password" name="password" id="password" required
                                class="h-[55px] rounded-md text-black dark:text-white border border-gray-200 dark:border-[#172036] bg-white dark:bg-[#0c1427] px-[17px] block w-full outline-0 transition-all placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-primary-500"
                                placeholder="Parolanızı girin">
                            <button class="absolute text-lg ltr:right-[20px] rtl:left-[20px] bottom-[12px] transition-all hover:text-primary-500"
                                id="toggleButton" type="button">
                                <i class="ri-eye-off-line"></i>
                            </button>
                        </div>

                        <label class="flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="remember" value="1"
                                class="ltr:mr-[8px] rtl:ml-[8px] accent-primary-500">
                            <span class="text-black dark:text-white">Beni hatırla</span>
                        </label>

                        <button type="submit"
                            class="md:text-md block w-full text-center transition-all rounded-md font-medium mt-[20px] md:mt-[25px] py-[12px] px-[25px] text-white bg-primary-500 hover:bg-primary-400">
                            <span class="flex items-center justify-center gap-[5px]">
                                <i class="material-symbols-outlined">login</i>
                                Giriş Yap
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
