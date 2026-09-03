@extends('admin.layout.app')
@section('admin.title', 'Dashboard')
@section('content')

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-[25px] mb-[25px]">
        <div class="xl:col-span-2">

            <!-- Analytics Overview -->
            <div class="trezo-card bg-white dark:bg-[#0c1427] mb-[25px] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px] sm:flex justify-between">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">
                            Analytics Overview
                        </h5>
                    </div>
                    <div class="trezo-card-subtitle mt-[13px] sm:mt-0">
                        <button type="button"
                            class="inline-block border border-gray-100 dark:border-[#172036] px-[15.5px] py-[1.5px] transition-all hover:bg-primary-50 dark:hover:bg-[#15203c] -mx-[3px] ltr:first:rounded-l-md rtl:first:rounded-r-lg ltr:last:rounded-r-md rtl:last:rounded-l-md">
                            Day
                        </button>
                        <button type="button"
                            class="inline-block border border-gray-100 dark:border-[#172036] px-[15.5px] py-[1.5px] transition-all bg-primary-50 dark:bg-[#15203c] hover:bg-primary-50 dark:hover:bg-[#15203c] -mx-[3px] ltr:first:rounded-l-md rtl:first:rounded-r-lg ltr:last:rounded-r-md rtl:last:rounded-l-md">
                            Week
                        </button>
                        <button type="button"
                            class="inline-block border border-gray-100 dark:border-[#172036] px-[15.5px] py-[1.5px] transition-all hover:bg-primary-50 dark:hover:bg-[#15203c] -mx-[3px] ltr:first:rounded-l-md rtl:first:rounded-r-lg ltr:last:rounded-r-md rtl:last:rounded-l-md">
                            Month
                        </button>
                    </div>
                </div>
                <div class="trezo-card-content -mt-[9px] sm:-mt-[17px] md:-mt-[22px]">
                    <p class="text-xs">
                        Track, Analyze, and Optimize Performance
                    </p>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-[15px] md:gap-[20px] lg:gap-[25px]">
                        <div class="lg:col-span-2">
                            <div class="-mb-[18px] ltr:-ml-[10px] rtl:-mr-[10px]">
                                <div id="analyticsOverviewChart"></div>
                            </div>
                        </div>
                        <div class="lg:col-span-1">
                            <ul class="lg:mt-[20px] lg:ltr:-ml-[20px] lg:rtl:-mr-[20px]">
                                <li class="mb-[24px] last:mb-0">
                                    <div class="flex justify-between">
                                        <div>
                                            <span class="block text-xs mb-[2px] text-black dark:text-white">
                                                New Users
                                            </span>
                                            <h5 class="!text-md !mb-0">
                                                19.5k
                                            </h5>
                                        </div>
                                        <div>
                                            <span class="block text-xs mb-[2px] text-black dark:text-white">
                                                Goal Reached
                                            </span>
                                            <h5
                                                class="!text-md !mb-0 !text-gray-500 dark:!text-gray-400 ltr:text-right rtl:text-left">
                                                75%
                                            </h5>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-[11px] flex w-full h-[4px] overflow-hidden rounded-md bg-primary-50 dark:bg-[#172036]">
                                        <div class="flex flex-col justify-center overflow-hidden bg-primary-500 rounded-md"
                                            style="width: 75%;"></div>
                                    </div>
                                </li>
                                <li class="mb-[24px] last:mb-0">
                                    <div class="flex justify-between">
                                        <div>
                                            <span class="block text-xs mb-[2px] text-black dark:text-white">
                                                Page Views
                                            </span>
                                            <h5 class="!text-md !mb-0">
                                                48k
                                            </h5>
                                        </div>
                                        <div>
                                            <span class="block text-xs mb-[2px] text-black dark:text-white">
                                                Goal Reached
                                            </span>
                                            <h5
                                                class="!text-md !mb-0 !text-gray-500 dark:!text-gray-400 ltr:text-right rtl:text-left">
                                                45%
                                            </h5>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-[11px] flex w-full h-[4px] overflow-hidden rounded-md bg-primary-50 dark:bg-[#172036]">
                                        <div class="flex flex-col justify-center overflow-hidden bg-purple-500 rounded-md"
                                            style="width: 45%;"></div>
                                    </div>
                                </li>
                                <li class="mb-[24px] last:mb-0">
                                    <div class="flex justify-between">
                                        <div>
                                            <span class="block text-xs mb-[2px] text-black dark:text-white">
                                                Page Sessions
                                            </span>
                                            <h5 class="!text-md !mb-0">
                                                33.3k
                                            </h5>
                                        </div>
                                        <div>
                                            <span class="block text-xs mb-[2px] text-black dark:text-white">
                                                Goal Reached
                                            </span>
                                            <h5
                                                class="!text-md !mb-0 !text-gray-500 dark:!text-gray-400 ltr:text-right rtl:text-left">
                                                55%
                                            </h5>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-[11px] flex w-full h-[4px] overflow-hidden rounded-md bg-primary-50 dark:bg-[#172036]">
                                        <div class="flex flex-col justify-center overflow-hidden bg-secondary-500 rounded-md"
                                            style="width: 55%;"></div>
                                    </div>
                                </li>
                                <li class="mb-[24px] last:mb-0">
                                    <div class="flex justify-between">
                                        <div>
                                            <span class="block text-xs mb-[2px] text-black dark:text-white">
                                                Bounce Rate
                                            </span>
                                            <h5 class="!text-md !mb-0">
                                                22.45%
                                            </h5>
                                        </div>
                                        <div>
                                            <span class="block text-xs mb-[2px] text-black dark:text-white">
                                                Goal Reached
                                            </span>
                                            <h5
                                                class="!text-md !mb-0 !text-gray-500 dark:!text-gray-400 ltr:text-right rtl:text-left">
                                                35%
                                            </h5>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-[11px] flex w-full h-[4px] overflow-hidden rounded-md bg-primary-50 dark:bg-[#172036]">
                                        <div class="flex flex-col justify-center overflow-hidden bg-danger-500 rounded-md"
                                            style="width: 35%;"></div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Analytics Stats -->
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md relative">
                <div class="trezo-card-content">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-[25px]">
                        <div
                            class="relative md:ltr:pl-[25px] md:ltr:first:pl-0 md:rtl:pr-[25px] md:rtl:first:pr-0 md:ltr:-mr-[25px] md:rtl:-ml-[25px] md:ltr:first:mr-0 md:rtl:first:ml-0">
                            <span class="block">
                                Website Visits
                            </span>
                            <h5 class="!mb-0 mt-[3px] !text-[20px]">
                                215.2k
                            </h5>
                            <div class="absolute -top-[28px] ltr:-right-[9px] rtl:-left-[9px] max-w-[120px]">
                                <div id="analyticsWebsiteVisitsChart"></div>
                            </div>
                            <div class="mt-[12px] flex items-center justify-between">
                                <span
                                    class="inline-block text-xs text-success-700 px-[9px] border border-success-300 bg-success-100 dark:bg-[#15203c] dark:border-[#15203c] rounded-xl">
                                    +10% Increase
                                </span>
                                <span class="block text-xs">
                                    Last 7 days
                                </span>
                            </div>
                        </div>
                        <div
                            class="relative md:ltr:pl-[25px] md:ltr:first:pl-0 md:rtl:pr-[25px] md:rtl:first:pr-0 md:ltr:-mr-[25px] md:rtl:-ml-[25px] md:ltr:first:mr-0 md:rtl:first:ml-0">
                            <span class="block">
                                New Registers
                            </span>
                            <h5 class="!mb-0 mt-[3px] !text-[20px]">
                                35.3k
                            </h5>
                            <div class="absolute -top-[28px] ltr:-right-[9px] rtl:-left-[9px] max-w-[120px]">
                                <div id="analyticsNewRegistersChart"></div>
                            </div>
                            <div class="mt-[12px] flex items-center justify-between">
                                <span
                                    class="inline-block text-xs text-success-700 px-[9px] border border-success-300 bg-success-100 dark:bg-[#15203c] dark:border-[#15203c] rounded-xl">
                                    +15% Increase
                                </span>
                                <span class="block text-xs">
                                    Last 7 days
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute ltr:right-0 rtl:left-0 top-1/2 -translate-y-1/2 hidden md:block">
                    <img src="assets/images/spring-fat.png" alt="spring-fat">
                </div>
            </div>

        </div>
        <div class="xl:col-span-1">

            <!-- Realtime Active Users -->
            <div class="trezo-card bg-primary-600 p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-content relative z-[1]">
                    <h6 class="!text-md !mb-[6px] !font-light !text-white">
                        Realtime Active Users
                    </h6>
                    <h2
                        class="!text-4xl !mb-[20px] md:!mb-[25px] lg:!mb-[45px] !text-white !leading-none !font-medium -tracking-[1px]">
                        4,890
                    </h2>
                    <span class="block text-white pb-[6px] border-b border-primary-500">
                        Page views per second
                    </span>
                    <div class="-mb-[10px] md:-mb-[2px] ltr:-ml-[29px] rtl:-mr-[29px] ltr:-mr-[17px] rtl:-ml-[17px]">
                        <div id="analyticsRealtimeActiveUsersChart"></div>
                    </div>
                    <span class="block text-white pb-[6px] border-b border-primary-500 font-semibold">
                        Top Active Pages
                    </span>
                    <ul class="mt-[10px] mb-[20px] md:mb-[25px]">
                        <li
                            class="text-white flex items-center justify-between pb-[4.8px] mb-[4.8px] border-b border-primary-500 last:mb-0">
                            <span class="block">
                                /reports/trends-analysis
                                <a href="#" class="ml-[5px] transition-all hover:text-gray-200">
                                    <i class="ri-external-link-line"></i>
                                </a>
                            </span>
                            <span class="block">
                                1520
                            </span>
                        </li>
                        <li
                            class="text-white flex items-center justify-between pb-[4.8px] mb-[4.8px] border-b border-primary-500 last:mb-0">
                            <span class="block">
                                /monitoring/user-activity
                                <a href="#" class="ml-[5px] transition-all hover:text-gray-200">
                                    <i class="ri-external-link-line"></i>
                                </a>
                            </span>
                            <span class="block">
                                980
                            </span>
                        </li>
                        <li
                            class="text-white flex items-center justify-between pb-[4.8px] mb-[4.8px] border-b border-primary-500 last:mb-0">
                            <span class="block">
                                /specific/industry-comparisons
                                <a href="#" class="ml-[5px] transition-all hover:text-gray-200">
                                    <i class="ri-external-link-line"></i>
                                </a>
                            </span>
                            <span class="block">
                                856
                            </span>
                        </li>
                        <li
                            class="text-white flex items-center justify-between pb-[4.8px] mb-[4.8px] border-b border-primary-500 last:mb-0">
                            <span class="block">
                                /global-reach-2023/statistics
                                <a href="#" class="ml-[5px] transition-all hover:text-gray-200">
                                    <i class="ri-external-link-line"></i>
                                </a>
                            </span>
                            <span class="block">
                                735
                            </span>
                        </li>
                        <li
                            class="text-white flex items-center justify-between pb-[4.8px] mb-[4.8px] border-b border-primary-500 last:mb-0">
                            <span class="block">
                                /security-alerts/2025-update
                                <a href="#" class="ml-[5px] transition-all hover:text-gray-200">
                                    <i class="ri-external-link-line"></i>
                                </a>
                            </span>
                            <span class="block">
                                520
                            </span>
                        </li>
                    </ul>
                    <div class="ltr:text-right rtl:text-left">
                        <a href="#"
                            class="inline-block px-[12px] py-[4px] rounded-md text-white font-medium border border-primary-400 transition-all hover:bg-primary-400">
                            <span class="inline-block relative ltr:pr-[17px] rtl:pl-[17px]">
                                All Real-Time Report
                                <i
                                    class="ri-arrow-right-s-line absolute ltr:-right-[6px] rtl:-left-[6px] text-[20px] top-1/2 -translate-y-1/2"></i>
                            </span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-[25px] mb-[25px]">
        <div class="lg:col-span-3">

            <!-- Browser Used By Users -->
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px] sm:flex items-center justify-between">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">
                            Browser Used By Users
                        </h5>
                    </div>
                    <div class="trezo-card-subtitle mt-[10px] sm:mt-0">
                        <div class="trezo-card-dropdown relative">
                            <button type="button"
                                class="trezo-card-dropdown-btn inline-block transition-all hover:text-primary-500"
                                id="dropdownToggleBtn">
                                <span
                                    class="inline-block relative ltr:pr-[17px] ltr:md:pr-[20px] rtl:pl-[17px] rtl:ml:pr-[20px]">
                                    Oct 01 - Oct 31, 2025
                                    <i
                                        class="ri-arrow-down-s-line text-lg absolute ltr:-right-[3px] rtl:-left-[3px] top-1/2 -translate-y-1/2"></i>
                                </span>
                            </button>
                            <ul
                                class="trezo-card-dropdown-menu transition-all bg-white shadow-3xl rounded-md top-full py-[15px] absolute ltr:right-0 rtl:left-0 w-[195px] z-[5] dark:bg-dark dark:shadow-none">
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Sep 01 - Sep 30, 2025
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Aug 01 - Aug 31, 2025
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Jul 01 - Jul 31, 2025
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="trezo-card-content">
                    <div class="table-responsive overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th
                                        class="font-medium text-xs text-center px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        Browser
                                    </th>
                                    <th
                                        class="font-medium text-xs ltr:text-right rtl:text-left px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        Users (%)
                                    </th>
                                    <th
                                        class="font-medium text-xs ltr:text-right rtl:text-left px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        Sessions
                                    </th>
                                    <th
                                        class="font-medium text-xs ltr:text-right rtl:text-left px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        Bounce Rate (%)
                                    </th>
                                    <th
                                        class="font-medium text-xs ltr:text-right rtl:text-left px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        Avg. Session Duration
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-black dark:text-white">
                                <tr>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <div class="flex items-center">
                                            <img alt="browser-image" src="assets/images/browsers/google-chrome.svg">
                                            <span class="block font-medium ltr:ml-[10px] rtl:mr-[10px]">
                                                Google Chrome
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        58.4%
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        12,345
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        34.2%
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        3m 15s
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <div class="flex items-center">
                                            <img alt="browser-image" src="assets/images/browsers/safari.svg">
                                            <span class="block font-medium ltr:ml-[10px] rtl:mr-[10px]">
                                                Safari
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        25.1%
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        5,289
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        40.5%
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        2m 45s
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <div class="flex items-center">
                                            <img alt="browser-image" src="assets/images/browsers/microsoft-edge.svg">
                                            <span class="block font-medium ltr:ml-[10px] rtl:mr-[10px]">
                                                Microsoft Edge
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        10.2%
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        2,134
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        29.8%
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        4m 05s
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <div class="flex items-center">
                                            <img alt="browser-image" src="assets/images/browsers/mozilla-firefox.svg">
                                            <span class="block font-medium ltr:ml-[10px] rtl:mr-[10px]">
                                                Mozilla Firefox
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        4.3%
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        897
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        38.0%
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        3m 00s
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <div class="flex items-center">
                                            <img alt="browser-image" src="assets/images/browsers/opera-mini.svg">
                                            <span class="block font-medium ltr:ml-[10px] rtl:mr-[10px]">
                                                Opera Mini
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        1.5%
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        315
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        35.7%
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        2m 30s
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <div class="flex items-center">
                                            <img alt="browser-image" src="assets/images/browsers/others.svg">
                                            <span class="block font-medium ltr:ml-[10px] rtl:mr-[10px]">
                                                Others
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        0.5%
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        105
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        42.1%
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        2m 10s
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
        <div class="lg:col-span-2">

            <!-- Device Sessions -->
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">
                            Device Sessions
                        </h5>
                    </div>
                    <div class="trezo-card-subtitle">
                        <div class="trezo-card-dropdown relative">
                            <button type="button"
                                class="trezo-card-dropdown-btn inline-block transition-all hover:text-primary-500"
                                id="dropdownToggleBtn">
                                <span
                                    class="inline-block relative ltr:pr-[17px] ltr:md:pr-[20px] rtl:pl-[17px] rtl:ml:pr-[20px]">
                                    Last Week
                                    <i
                                        class="ri-arrow-down-s-line text-lg absolute ltr:-right-[3px] rtl:-left-[3px] top-1/2 -translate-y-1/2"></i>
                                </span>
                            </button>
                            <ul
                                class="trezo-card-dropdown-menu transition-all bg-white shadow-3xl rounded-md top-full py-[15px] absolute ltr:right-0 rtl:left-0 w-[195px] z-[5] dark:bg-dark dark:shadow-none">
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Last Day
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Last Week
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Last Month
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Last Year
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="trezo-card-content">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-[25px] items-center">
                        <div class="-mb-[7px] md:ltr:-ml-[35px] md:rtl:-mr-[35px]">
                            <div id="analyticsDeviceSessionsChart"></div>
                        </div>
                        <ul class="lg:ltr:-ml-[30px] lg:rtl:-mr-[30px]">
                            <li
                                class="flex relative justify-between ltr:pl-[22px] rtl:pr-[22px] border-b border-gray-100 dark:border-[#172036] py-[7px] first:border-t">
                                <div
                                    class="bg-success-500 absolute ltr:left-0 rtl:right-0 w-[10px] h-[10px] top-1/2 -translate-y-1/2 rounded-full">
                                </div>
                                <div>
                                    <span class="block text-xs mb-px">
                                        Desktop
                                    </span>
                                    <h6 class="!mb-0 !text-md !font-semibold">
                                        45.2%
                                    </h6>
                                </div>
                                <div>
                                    <span class="block text-xs mb-px">
                                        Sessions
                                    </span>
                                    <h6 class="!mb-0 !font-semibold">
                                        8,732
                                    </h6>
                                </div>
                            </li>
                            <li
                                class="flex relative justify-between ltr:pl-[22px] rtl:pr-[22px] border-b border-gray-100 dark:border-[#172036] py-[7px] first:border-t">
                                <div
                                    class="bg-primary-400 absolute ltr:left-0 rtl:right-0 w-[10px] h-[10px] top-1/2 -translate-y-1/2 rounded-full">
                                </div>
                                <div>
                                    <span class="block text-xs mb-px">
                                        Mobile
                                    </span>
                                    <h6 class="!mb-0 !text-md !font-semibold">
                                        48.7%
                                    </h6>
                                </div>
                                <div>
                                    <span class="block text-xs mb-px">
                                        Sessions
                                    </span>
                                    <h6 class="!mb-0 !font-semibold">
                                        9,416
                                    </h6>
                                </div>
                            </li>
                            <li
                                class="flex relative justify-between ltr:pl-[22px] rtl:pr-[22px] border-b border-gray-100 dark:border-[#172036] py-[7px] first:border-t">
                                <div
                                    class="bg-purple-400 absolute ltr:left-0 rtl:right-0 w-[10px] h-[10px] top-1/2 -translate-y-1/2 rounded-full">
                                </div>
                                <div>
                                    <span class="block text-xs mb-px">
                                        Tablet
                                    </span>
                                    <h6 class="!mb-0 !text-md !font-semibold">
                                        32.3%
                                    </h6>
                                </div>
                                <div>
                                    <span class="block text-xs mb-px">
                                        Sessions
                                    </span>
                                    <h6 class="!mb-0 !font-semibold">
                                        1,042
                                    </h6>
                                </div>
                            </li>
                            <li
                                class="flex relative justify-between ltr:pl-[22px] rtl:pr-[22px] border-b border-gray-100 dark:border-[#172036] py-[7px] first:border-t">
                                <div
                                    class="bg-orange-400 absolute ltr:left-0 rtl:right-0 w-[10px] h-[10px] top-1/2 -translate-y-1/2 rounded-full">
                                </div>
                                <div>
                                    <span class="block text-xs mb-px">
                                        Others
                                    </span>
                                    <h6 class="!mb-0 !text-md !font-semibold">
                                        15.4%
                                    </h6>
                                </div>
                                <div>
                                    <span class="block text-xs mb-px">
                                        Sessions
                                    </span>
                                    <h6 class="!mb-0 !font-semibold">
                                        135
                                    </h6>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <p class="text-xs mt-[20px] md:mt-[24px]">
                        This data helps you understand which devices are most commonly used and how engagement metrics vary
                        between them.
                    </p>
                </div>
            </div>

        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-[25px] mb-[25px]">

        <!-- Clicks -->
        <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-content relative">
                <span class="block mb-[5px]">
                    Clicks
                </span>
                <div class="flex items-center">
                    <h6 class="!mb-0 !text-[20px]">
                        4,500
                    </h6>
                    <span
                        class="inline-block font-medium relative text-[10px] ltr:ml-[11px] rtl:mr-[11px] rounded-sm bg-success-100 text-success-600 dark:bg-[#15203c] py-[1.5px] ltr:pl-[25px] rtl:pr-[25px] ltr:pr-[7px] rtl:pl-[7px]">
                        <i
                            class="ri-arrow-up-s-fill absolute ltr:left-[2px] rtl:right-[2px] text-[22px] top-1/2 -translate-y-1/2"></i>
                        37.5%
                    </span>
                </div>
                <span class="text-xs block mt-[29px]">
                    Last 30 days
                </span>
                <div
                    class="absolute max-w-[140px] md:max-w-[160px] ltr:-right-[9px] rtl:-left-[9px] top-1/2 -translate-y-1/2">
                    <div id="analyticsClicksChart"></div>
                </div>
            </div>
        </div>

        <!-- Impressions -->
        <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-content relative">
                <span class="block mb-[5px]">
                    Impressions
                </span>
                <div class="flex items-center">
                    <h6 class="!mb-0 !text-[20px]">
                        12,000
                    </h6>
                    <span
                        class="inline-block font-medium relative text-[10px] ltr:ml-[11px] rtl:mr-[11px] rounded-sm bg-danger-100 text-danger-500 dark:bg-[#15203c] py-[1.5px] ltr:pl-[25px] rtl:pr-[25px] ltr:pr-[7px] rtl:pl-[7px]">
                        <i
                            class="ri-arrow-down-s-fill absolute ltr:left-[2px] rtl:right-[2px] text-[22px] top-1/2 -translate-y-1/2"></i>
                        10.5%
                    </span>
                </div>
                <span class="text-xs block mt-[29px]">
                    Last 30 days
                </span>
                <div
                    class="absolute max-w-[140px] md:max-w-[160px] ltr:-right-[9px] rtl:-left-[9px] top-1/2 -translate-y-1/2">
                    <div id="analyticsImpressionsChart"></div>
                </div>
            </div>
        </div>

        <!-- Sessions -->
        <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
            <div class="trezo-card-content relative">
                <span class="block mb-[5px]">
                    Sessions
                </span>
                <div class="flex items-center">
                    <h6 class="!mb-0 !text-[20px]">
                        3,800
                    </h6>
                    <span
                        class="inline-block font-medium relative text-[10px] ltr:ml-[11px] rtl:mr-[11px] rounded-sm bg-success-100 text-success-600 dark:bg-[#15203c] py-[1.5px] ltr:pl-[25px] rtl:pr-[25px] ltr:pr-[7px] rtl:pl-[7px]">
                        <i
                            class="ri-arrow-up-s-fill absolute ltr:left-[2px] rtl:right-[2px] text-[22px] top-1/2 -translate-y-1/2"></i>
                        17.8%
                    </span>
                </div>
                <span class="text-xs block mt-[29px]">
                    Last 30 days
                </span>
                <div
                    class="absolute max-w-[140px] md:max-w-[160px] ltr:-right-[9px] rtl:-left-[9px] top-1/2 -translate-y-1/2">
                    <div id="analyticsSessionsChart"></div>
                </div>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-[25px] mb-[25px]">
        <div class="lg:col-span-2">

            <!-- Sessions by Channel -->
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">
                            Sessions by Channel
                        </h5>
                    </div>
                    <div class="trezo-card-subtitle">
                        <div class="trezo-card-dropdown relative">
                            <button type="button"
                                class="trezo-card-dropdown-btn inline-block transition-all hover:text-primary-500"
                                id="dropdownToggleBtn">
                                <span
                                    class="inline-block relative ltr:pr-[17px] ltr:md:pr-[20px] rtl:pl-[17px] rtl:ml:pr-[20px]">
                                    Last 30 Days
                                    <i
                                        class="ri-arrow-down-s-line text-lg absolute ltr:-right-[3px] rtl:-left-[3px] top-1/2 -translate-y-1/2"></i>
                                </span>
                            </button>
                            <ul
                                class="trezo-card-dropdown-menu transition-all bg-white shadow-3xl rounded-md top-full py-[15px] absolute ltr:right-0 rtl:left-0 w-[195px] z-[5] dark:bg-dark dark:shadow-none">
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Last Day
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Last Week
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Last Month
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Last Year
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="trezo-card-content">
                    <div id="analyticsSessionsByChannelChart"></div>
                    <ul class="text-center my-[10px]">
                        <li
                            class="inline-block ltr:text-left rtl:text-right mx-[13px] lg:mx-[10px] xl:mx-[13px] mt-[15px]">
                            <span class="block text-xs mb-[8px] flex items-center justify-center">
                                <span
                                    class="block bg-secondary-500 w-[11px] h-[11px] ltr:mr-[8px] rtl:ml-[8px] rounded-sm relative -top-[1px]"></span>
                                Email
                            </span>
                            <h6 class="!mb-0 !leading-none !font-medium !text-lg ltr:ml-[19px] rtl:mr-[19px]">
                                976
                            </h6>
                        </li>
                        <li
                            class="inline-block ltr:text-left rtl:text-right mx-[13px] lg:mx-[10px] xl:mx-[13px] mt-[15px]">
                            <span class="block text-xs mb-[8px] flex items-center justify-center">
                                <span
                                    class="block bg-success-500 w-[11px] h-[11px] ltr:mr-[8px] rtl:ml-[8px] rounded-sm relative -top-[1px]"></span>
                                Organic Search
                            </span>
                            <h6 class="!mb-0 !leading-none !font-medium !text-lg ltr:ml-[19px] rtl:mr-[19px]">
                                651
                            </h6>
                        </li>
                        <li
                            class="inline-block ltr:text-left rtl:text-right mx-[13px] lg:mx-[10px] xl:mx-[13px] mt-[15px]">
                            <span class="block text-xs mb-[8px] flex items-center justify-center">
                                <span
                                    class="block bg-purple-500 w-[11px] h-[11px] ltr:mr-[8px] rtl:ml-[8px] rounded-sm relative -top-[1px]"></span>
                                Direct Browse
                            </span>
                            <h6 class="!mb-0 !leading-none !font-medium !text-lg ltr:ml-[19px] rtl:mr-[19px]">
                                818
                            </h6>
                        </li>
                        <li
                            class="inline-block ltr:text-left rtl:text-right mx-[13px] lg:mx-[10px] xl:mx-[13px] mt-[15px]">
                            <span class="block text-xs mb-[8px] flex items-center justify-center">
                                <span
                                    class="block bg-secondary-300 w-[11px] h-[11px] ltr:mr-[8px] rtl:ml-[8px] rounded-sm relative -top-[1px]"></span>
                                Paid Search
                            </span>
                            <h6 class="!mb-0 !leading-none !font-medium !text-lg ltr:ml-[19px] rtl:mr-[19px]">
                                459
                            </h6>
                        </li>
                        <li
                            class="inline-block ltr:text-left rtl:text-right mx-[13px] lg:mx-[10px] xl:mx-[13px] mt-[15px]">
                            <span class="block text-xs mb-[8px] flex items-center justify-center">
                                <span
                                    class="block bg-primary-500 w-[11px] h-[11px] ltr:mr-[8px] rtl:ml-[8px] rounded-sm relative -top-[1px]"></span>
                                Social
                            </span>
                            <h6 class="!mb-0 !leading-none !font-medium !text-lg ltr:ml-[19px] rtl:mr-[19px]">
                                320
                            </h6>
                        </li>
                        <li
                            class="inline-block ltr:text-left rtl:text-right mx-[13px] lg:mx-[10px] xl:mx-[13px] mt-[15px]">
                            <span class="block text-xs mb-[8px] flex items-center justify-center">
                                <span
                                    class="block bg-orange-500 w-[11px] h-[11px] ltr:mr-[8px] rtl:ml-[8px] rounded-sm relative -top-[1px]"></span>
                                Referral
                            </span>
                            <h6 class="!mb-0 !leading-none !font-medium !text-lg ltr:ml-[19px] rtl:mr-[19px]">
                                209
                            </h6>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
        <div class="lg:col-span-3">

            <!-- Clicks/Impressions by Keywords -->
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px] sm:flex items-center justify-between">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">
                            Clicks/Impressions by Keywords
                        </h5>
                    </div>
                    <div class="trezo-card-subtitle mt-[10px] sm:mt-0">
                        <div class="trezo-card-dropdown relative">
                            <button type="button"
                                class="trezo-card-dropdown-btn inline-block transition-all hover:text-primary-500"
                                id="dropdownToggleBtn">
                                <span
                                    class="inline-block relative ltr:pr-[17px] ltr:md:pr-[20px] rtl:pl-[17px] rtl:ml:pr-[20px]">
                                    Oct 01 - Oct 31, 2025
                                    <i
                                        class="ri-arrow-down-s-line text-lg absolute ltr:-right-[3px] rtl:-left-[3px] top-1/2 -translate-y-1/2"></i>
                                </span>
                            </button>
                            <ul
                                class="trezo-card-dropdown-menu transition-all bg-white shadow-3xl rounded-md top-full py-[15px] absolute ltr:right-0 rtl:left-0 w-[195px] z-[5] dark:bg-dark dark:shadow-none">
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Sep 01 - Sep 30, 2025
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Aug 01 - Aug 31, 2025
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Jul 01 - Jul 31, 2025
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="trezo-card-content">
                    <div class="table-responsive overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th
                                        class="font-medium text-xs ltr:text-left rtl:text-right px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        #
                                    </th>
                                    <th
                                        class="font-medium text-xs ltr:text-left rtl:text-right px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        Dimension
                                    </th>
                                    <th
                                        class="font-medium text-xs ltr:text-right rtl:text-left px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        Impressions
                                    </th>
                                    <th
                                        class="font-medium text-xs ltr:text-right rtl:text-left px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        Clicks
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-black dark:text-white">
                                <tr>
                                    <td
                                        class="font-medium text-gray-500 dark:text-gray-400 ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        1
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        data metrics
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-success-600 top-1/2 -translate-y-1/2">
                                                trending_up
                                            </i>
                                            949
                                        </span>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-danger-500 top-1/2 -translate-y-1/2">
                                                trending_down
                                            </i>
                                            656
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="font-medium text-gray-500 dark:text-gray-400 ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        2
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        sales metrics
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-danger-500 top-1/2 -translate-y-1/2">
                                                trending_down
                                            </i>
                                            842
                                        </span>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-success-600 top-1/2 -translate-y-1/2">
                                                trending_up
                                            </i>
                                            420
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="font-medium text-gray-500 dark:text-gray-400 ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        3
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        analytics dashboard
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-success-600 top-1/2 -translate-y-1/2">
                                                trending_up
                                            </i>
                                            640
                                        </span>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-danger-500 top-1/2 -translate-y-1/2">
                                                trending_down
                                            </i>
                                            534
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="font-medium text-gray-500 dark:text-gray-400 ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        4
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        saas metrics
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-success-600 top-1/2 -translate-y-1/2">
                                                trending_up
                                            </i>
                                            865
                                        </span>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-danger-500 top-1/2 -translate-y-1/2">
                                                trending_down
                                            </i>
                                            560
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="font-medium text-gray-500 dark:text-gray-400 ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        5
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        hubspot analytics
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-danger-500 top-1/2 -translate-y-1/2">
                                                trending_down
                                            </i>
                                            535
                                        </span>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-success-600 top-1/2 -translate-y-1/2">
                                                trending_up
                                            </i>
                                            328
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="font-medium text-gray-500 dark:text-gray-400 ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        6
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        smart goals
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-success-600 top-1/2 -translate-y-1/2">
                                                trending_up
                                            </i>
                                            756
                                        </span>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-danger-500 top-1/2 -translate-y-1/2">
                                                trending_down
                                            </i>
                                            235
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="font-medium text-gray-500 dark:text-gray-400 ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        7
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        google analytics
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-success-600 top-1/2 -translate-y-1/2">
                                                trending_up
                                            </i>
                                            578
                                        </span>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-danger-500 top-1/2 -translate-y-1/2">
                                                trending_down
                                            </i>
                                            456
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="font-medium text-gray-500 dark:text-gray-400 ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        8
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        trezo dashboard
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-success-600 top-1/2 -translate-y-1/2">
                                                trending_up
                                            </i>
                                            660
                                        </span>
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[10px] border-b border-gray-100 dark:border-[#172036]">
                                        <span class="inline-block relative ltr:pl-[28px] rtl:pr-[28px]">
                                            <i
                                                class="material-symbols-outlined !text-[20px] absolute ltr:left-0 rtl:right-0 text-danger-500 top-1/2 -translate-y-1/2">
                                                trending_down
                                            </i>
                                            478
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="pt-[9px] sm:flex sm:items-center justify-between">
                        <p class="!mb-0 text-sm">
                            Showing 8 of 36 results
                        </p>
                        <ol class="mt-[10px] sm:mt-0">
                            <li class="inline-block mx-[1px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                                <a href="javascript:void(0);"
                                    class="w-[31px] h-[31px] block leading-[29px] relative text-center rounded-md border border-gray-100 dark:border-[#172036] transition-all hover:bg-primary-500 hover:text-white hover:border-primary-500">
                                    <span class="opacity-0">
                                        0
                                    </span>
                                    <i class="material-symbols-outlined left-0 right-0 absolute top-1/2 -translate-y-1/2">
                                        chevron_left
                                    </i>
                                </a>
                            </li>
                            <li class="inline-block mx-[1px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                                <a href="javascript:void(0);"
                                    class="w-[31px] h-[31px] block leading-[29px] relative text-center rounded-md border border-primary-500 bg-primary-500 text-white">
                                    1
                                </a>
                            </li>
                            <li class="inline-block mx-[1px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                                <a href="javascript:void(0);"
                                    class="w-[31px] h-[31px] block leading-[29px] relative text-center rounded-md border border-gray-100 dark:border-[#172036] transition-all hover:bg-primary-500 hover:text-white hover:border-primary-500">
                                    2
                                </a>
                            </li>
                            <li class="inline-block mx-[1px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                                <a href="javascript:void(0);"
                                    class="w-[31px] h-[31px] block leading-[29px] relative text-center rounded-md border border-gray-100 dark:border-[#172036] transition-all hover:bg-primary-500 hover:text-white hover:border-primary-500">
                                    3
                                </a>
                            </li>
                            <li class="inline-block mx-[1px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                                <a href="javascript:void(0);"
                                    class="w-[31px] h-[31px] block leading-[29px] relative text-center rounded-md border border-gray-100 dark:border-[#172036] transition-all hover:bg-primary-500 hover:text-white hover:border-primary-500">
                                    4
                                </a>
                            </li>
                            <li class="inline-block mx-[1px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                                <a href="javascript:void(0);"
                                    class="w-[31px] h-[31px] block leading-[29px] relative text-center rounded-md border border-gray-100 dark:border-[#172036] transition-all hover:bg-primary-500 hover:text-white hover:border-primary-500">
                                    <span class="opacity-0">
                                        0
                                    </span>
                                    <i class="material-symbols-outlined left-0 right-0 absolute top-1/2 -translate-y-1/2">
                                        chevron_right
                                    </i>
                                </a>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-[25px] mb-[25px]">
        <div class="lg:col-span-2">

            <!-- Top Browsing Pages Today -->
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px] sm:flex items-center justify-between">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">
                            Top Browsing Pages Today
                        </h5>
                    </div>
                    <div class="trezo-card-subtitle mt-[15px] sm:mt-0">
                        <form class="relative sm:w-[265px]">
                            <label
                                class="leading-none absolute ltr:left-[13px] rtl:right-[13px] text-black dark:text-white mt-px top-1/2 -translate-y-1/2">
                                <i class="material-symbols-outlined !text-[20px]">
                                    search
                                </i>
                            </label>
                            <input type="text" placeholder="Search here....."
                                class="bg-gray-50 border border-gray-50 h-[36px] text-xs rounded-md w-full block text-black pt-[11px] pb-[12px] ltr:pl-[38px] rtl:pr-[38px] ltr:pr-[13px] ltr:md:pr-[16px] rtl:pl-[13px] rtl:md:pl-[16px] placeholder:text-gray-500 outline-0 dark:bg-[#15203c] dark:text-white dark:border-[#15203c] dark:placeholder:text-gray-400">
                        </form>
                    </div>
                </div>
                <div class="trezo-card-content">
                    <div class="table-responsive overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th
                                        class="font-medium text-xs ltr:text-left rtl:text-right px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        Page URL
                                    </th>
                                    <th
                                        class="font-medium text-xs ltr:text-left rtl:text-right px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        Source
                                    </th>
                                    <th
                                        class="font-medium text-xs ltr:text-left rtl:text-right px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        Avg Time
                                    </th>
                                    <th
                                        class="font-medium text-xs ltr:text-right rtl:text-left px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        Page Views
                                    </th>
                                    <th
                                        class="font-medium text-xs ltr:text-right rtl:text-left px-[14px] pb-[7px] whitespace-nowrap ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0">
                                        Bounce Rate (%)
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-black dark:text-white">
                                <tr>
                                    <td
                                        class="ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        <a href="#"
                                            class="inline-block text-secondary-500 transition-all hover:text-secondary-400"
                                            target="_blank">
                                            /dashboard-overview
                                        </a>
                                    </td>
                                    <td
                                        class="ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        Organic
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        3m 45s
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        350
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        30.5%
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        <a href="#"
                                            class="inline-block text-secondary-500 transition-all hover:text-secondary-400"
                                            target="_blank">
                                            /custom-reports/generate
                                        </a>
                                    </td>
                                    <td
                                        class="ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        Paid
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        7m 00s
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        400
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        24.9%
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        <a href="#"
                                            class="inline-block text-secondary-500 transition-all hover:text-secondary-400"
                                            target="_blank">
                                            /export-data
                                        </a>
                                    </td>
                                    <td
                                        class="ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        Direct
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        2m 30s
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        125
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        50.0%
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        <a href="#"
                                            class="inline-block text-secondary-500 transition-all hover:text-secondary-400"
                                            target="_blank">
                                            /realtime-users
                                        </a>
                                    </td>
                                    <td
                                        class="ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        Referral
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        3m 00s
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        190
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        40.2%
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        <a href="#"
                                            class="inline-block text-secondary-500 transition-all hover:text-secondary-400"
                                            target="_blank">
                                            /account-preferences
                                        </a>
                                    </td>
                                    <td
                                        class="ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        Organic
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        2m 50s
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        180
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        42.1%
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        class="ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        <a href="#"
                                            class="inline-block text-secondary-500 transition-all hover:text-secondary-400"
                                            target="_blank">
                                            /apis-and-reports
                                        </a>
                                    </td>
                                    <td
                                        class="ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        Paid
                                    </td>
                                    <td
                                        class="font-medium ltr:text-left rtl:text-right whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        4m 15s
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        320
                                    </td>
                                    <td
                                        class="font-medium ltr:text-right rtl:text-left whitespace-nowrap px-[14px] ltr:first:pl-0 rtl:first:pr-0 ltr:last:pr-0 rtl:last:pl-0 py-[9px] border-b border-gray-100 dark:border-[#172036]">
                                        28.7%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="pt-[10px] sm:flex sm:items-center justify-between">
                        <p class="!mb-0 text-sm">
                            Showing 6 of 36 results
                        </p>
                        <ol class="mt-[10px] sm:mt-0">
                            <li class="inline-block mx-[1px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                                <a href="javascript:void(0);"
                                    class="w-[31px] h-[31px] block leading-[29px] relative text-center rounded-md border border-gray-100 dark:border-[#172036] transition-all hover:bg-primary-500 hover:text-white hover:border-primary-500">
                                    <span class="opacity-0">
                                        0
                                    </span>
                                    <i class="material-symbols-outlined left-0 right-0 absolute top-1/2 -translate-y-1/2">
                                        chevron_left
                                    </i>
                                </a>
                            </li>
                            <li class="inline-block mx-[1px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                                <a href="javascript:void(0);"
                                    class="w-[31px] h-[31px] block leading-[29px] relative text-center rounded-md border border-primary-500 bg-primary-500 text-white">
                                    1
                                </a>
                            </li>
                            <li class="inline-block mx-[1px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                                <a href="javascript:void(0);"
                                    class="w-[31px] h-[31px] block leading-[29px] relative text-center rounded-md border border-gray-100 dark:border-[#172036] transition-all hover:bg-primary-500 hover:text-white hover:border-primary-500">
                                    2
                                </a>
                            </li>
                            <li class="inline-block mx-[1px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                                <a href="javascript:void(0);"
                                    class="w-[31px] h-[31px] block leading-[29px] relative text-center rounded-md border border-gray-100 dark:border-[#172036] transition-all hover:bg-primary-500 hover:text-white hover:border-primary-500">
                                    3
                                </a>
                            </li>
                            <li class="inline-block mx-[1px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                                <a href="javascript:void(0);"
                                    class="w-[31px] h-[31px] block leading-[29px] relative text-center rounded-md border border-gray-100 dark:border-[#172036] transition-all hover:bg-primary-500 hover:text-white hover:border-primary-500">
                                    4
                                </a>
                            </li>
                            <li class="inline-block mx-[1px] ltr:first:ml-0 ltr:last:mr-0 rtl:first:mr-0 rtl:last:ml-0">
                                <a href="javascript:void(0);"
                                    class="w-[31px] h-[31px] block leading-[29px] relative text-center rounded-md border border-gray-100 dark:border-[#172036] transition-all hover:bg-primary-500 hover:text-white hover:border-primary-500">
                                    <span class="opacity-0">
                                        0
                                    </span>
                                    <i class="material-symbols-outlined left-0 right-0 absolute top-1/2 -translate-y-1/2">
                                        chevron_right
                                    </i>
                                </a>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

        </div>
        <div class="lg:col-span-1">

            <!-- Users by Country -->
            <div class="trezo-card bg-white dark:bg-[#0c1427] p-[20px] md:p-[25px] rounded-md">
                <div class="trezo-card-header mb-[20px] md:mb-[25px] flex items-center justify-between">
                    <div class="trezo-card-title">
                        <h5 class="!mb-0">
                            Users by Country
                        </h5>
                    </div>
                    <div class="trezo-card-subtitle">
                        <div class="trezo-card-dropdown relative">
                            <button type="button"
                                class="trezo-card-dropdown-btn inline-block transition-all hover:text-primary-500"
                                id="dropdownToggleBtn">
                                <span
                                    class="inline-block relative ltr:pr-[17px] ltr:md:pr-[20px] rtl:pl-[17px] rtl:ml:pr-[20px]">
                                    Last Week
                                    <i
                                        class="ri-arrow-down-s-line text-lg absolute ltr:-right-[3px] rtl:-left-[3px] top-1/2 -translate-y-1/2"></i>
                                </span>
                            </button>
                            <ul
                                class="trezo-card-dropdown-menu transition-all bg-white shadow-3xl rounded-md top-full py-[15px] absolute ltr:right-0 rtl:left-0 w-[195px] z-[5] dark:bg-dark dark:shadow-none">
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Last Day
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Last Week
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Last Month
                                    </button>
                                </li>
                                <li>
                                    <button type="button"
                                        class="block w-full transition-all text-black ltr:text-left rtl:text-right relative py-[8px] px-[20px] hover:bg-gray-50 dark:text-white dark:hover:bg-black">
                                        Last Year
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="trezo-card-content">
                    <div class="flex items-center justify-center min-h-[162px] md:mt-[37px]">
                        <div id="locationsJsVectorMap"></div>
                    </div>
                    <ul class="grid grid-cols-2">
                        <li>
                            <div
                                class="relative mt-[14px] md:mt-[16px] ltr:pl-[30px] rtl:pr-[30px] pt-[14px] md:pt-[16px]">
                                <img src="assets/images/flags/usa.svg"
                                    class="absolute top-[18px] ltr:left-0 rtl:right-0 w-[20px]" alt="usa">
                                <span class="block font-medium text-xs mb-[4px]">
                                    United States
                                </span>
                                <h6 class="!mb-0 !text-md !font-semibold">
                                    12,800 <span class="text-xs text-gray-500 dark:text-gray-400">35.6%</span>
                                </h6>
                            </div>
                        </li>
                        <li>
                            <div
                                class="relative mt-[14px] md:mt-[16px] ltr:pl-[30px] rtl:pr-[30px] pt-[14px] md:pt-[16px]">
                                <img src="assets/images/flags/uk.svg"
                                    class="absolute top-[18px] ltr:left-0 rtl:right-0 w-[20px]" alt="usa">
                                <span class="block font-medium text-xs mb-[4px]">
                                    United Kingdom
                                </span>
                                <h6 class="!mb-0 !text-md !font-semibold">
                                    6,750 <span class="text-xs text-gray-500 dark:text-gray-400">18.7%</span>
                                </h6>
                            </div>
                        </li>
                        <li>
                            <div
                                class="relative border-t border-gray-100 dark:border-[#172036] mt-[14px] md:mt-[16px] ltr:pl-[30px] rtl:pr-[30px] pt-[14px] md:pt-[16px]">
                                <img src="assets/images/flags/canada.svg"
                                    class="absolute top-[18px] ltr:left-0 rtl:right-0 w-[20px]" alt="usa">
                                <span class="block font-medium text-xs mb-[4px]">
                                    Canada
                                </span>
                                <h6 class="!mb-0 !text-md !font-semibold">
                                    2,500 <span class="text-xs text-gray-500 dark:text-gray-400">6.3%</span>
                                </h6>
                            </div>
                        </li>
                        <li>
                            <div
                                class="relative border-t border-gray-100 dark:border-[#172036] mt-[14px] md:mt-[16px] ltr:pl-[30px] rtl:pr-[30px] pt-[14px] md:pt-[16px]">
                                <img src="assets/images/flags/australia.svg"
                                    class="absolute top-[18px] ltr:left-0 rtl:right-0 w-[20px]" alt="usa">
                                <span class="block font-medium text-xs mb-[4px]">
                                    Australia
                                </span>
                                <h6 class="!mb-0 !text-md !font-semibold">
                                    2,200 <span class="text-xs text-gray-500 dark:text-gray-400">5.4%</span>
                                </h6>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

@endsection
