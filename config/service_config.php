<?php
return [
    'activationKeywords' => [
        'start bd' => ['offerId' => '9913110012', 'serviceName' => '16303_momagic_ivr_daily_auto'],
        'start bw' => ['offerId' => '9913110013', 'serviceName' => '16303_momagic_ivr_Weekly_auto'],
        'start bm' => ['offerId' => '9913110014', 'serviceName' => '16303_momagic_ivr_monthly_auto'],
    ],
    'deactivationKeywords' => [
        'stop bd' => ['offerId' => '9913110012', 'serviceName' => '16303_momagic_ivr_daily_auto'],
        'stop bw' => ['offerId' => '9913110013', 'serviceName' => '16303_momagic_ivr_Weekly_auto'],
        'stop bm' => ['offerId' => '9913110014', 'serviceName' => '16303_momagic_ivr_monthly_auto'],
    ],
    'serviceKeywordMap' => [
        '16303_momagic_ivr_daily_auto' => 'DAILY',
        '16303_momagic_ivr_Weekly_auto' => 'WEEKLY',
        '16303_momagic_ivr_monthly_auto' => 'MONTHLY',
    ],
    'smsTemplates' => [
        // Low balance (parking) ACTIVATION_PARKING(sub2)
        'ACTIVATION_PARKING' => [
            '16303_momagic_ivr_daily_auto' => 'দুঃখিত! আপনার ব্যালেন্স কম থাকায় এই মুহুর্তে  Momagic IVR Daily সার্ভিসটি চালু করা সম্ভব হয় নি। দয়া করে রিচার্জ করুন এবং যথেষ্ট পরিমাণ ব্যালেন্স রেখে পুনরায় চেষ্টা করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ১০টা পর্যন্ত)',
            '16303_momagic_ivr_Weekly_auto' => 'দুঃখিত! আপনার ব্যালেন্স কম থাকায় এই মুহুর্তে  Momagic IVR Weekly সার্ভিসটি চালু করা সম্ভব হয় নি। দয়া করে রিচার্জ করুন এবং যথেষ্ট পরিমাণ ব্যালেন্স রেখে পুনরায় চেষ্টা করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ১০টা পর্যন্ত)',
            '16303_momagic_ivr_monthly_auto' => 'দুঃখিত! আপনার ব্যালেন্স কম থাকায় এই মুহুর্তে  Momagic IVR Monthly সার্ভিসটি চালু করা সম্ভব হয় নি। দয়া করে রিচার্জ করুন এবং যথেষ্ট পরিমাণ ব্যালেন্স রেখে পুনরায় চেষ্টা করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ১০টা পর্যন্ত)',
        ],
        // Activation Success ACTIVE (Sub1)
        'ACTIVE' => [
            '16303_momagic_ivr_daily_auto' => ' Momagic IVR Daily সার্ভিসটি সফলভাবে চালু হয়েছে। চার্জ: 2.78 টাকা/Daily (ট্যাক্স সহ)। অটো-রিনিউ প্রযোজ্য। বন্ধ করতে এসএমএস করুন STOP BD লিখে 16303 নাম্বারে। পরবর্তী অটোরিনিউ: {date}।  হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ১০টা পর্যন্ত)',
            '16303_momagic_ivr_Weekly_auto' => ' Momagic IVR Weekly সার্ভিসটি সফলভাবে চালু হয়েছে। চার্জ: 2.78 টাকা/Daily (ট্যাক্স সহ)। অটো-রিনিউ প্রযোজ্য। বন্ধ করতে এসএমএস করুন STOP BW লিখে 16303 নাম্বারে। পরবর্তী অটোরিনিউ: {date}।  হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ১০টা পর্যন্ত)',
            '16303_momagic_ivr_monthly_auto' => ' Momagic IVR Monthly সার্ভিসটি সফলভাবে চালু হয়েছে। চার্জ: 2.78 টাকা/Daily (ট্যাক্স সহ)। অটো-রিনিউ প্রযোজ্য। বন্ধ করতে এসএমএস করুন STOP BM লিখে 16303 নাম্বারে। পরবর্তী অটোরিনিউ: {date}।  হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ১০টা পর্যন্ত)',
        ],
        // Deactivate Success SUSPEND (SUB3) or DEACTIVE (UNSUB1)
        'DEACTIVATE' => [
            '16303_momagic_ivr_daily_auto' => ' Momagic IVR Daily সার্ভিসটি সফলভাবে বন্ধ হয়েছে। পুনরায় চালু করতে এসএমএস করুন START BD লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ১০টা পর্যন্ত)',
            '16303_momagic_ivr_Weekly_auto' => ' Momagic IVR Weekly সার্ভিসটি সফলভাবে বন্ধ হয়েছে। পুনরায় চালু করতে এসএমএস করুন START BW লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ১০টা পর্যন্ত)',
            '16303_momagic_ivr_monthly_auto' => ' Momagic IVR Monthly সার্ভিসটি সফলভাবে বন্ধ হয়েছে। পুনরায় চালু করতে এসএমএস করুন START BM লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ১০টা পর্যন্ত)',
        ],
        'ALREADY_SUBSCRIBED' => [
            '16303_momagic_ivr_daily_auto' => 'আপনার Momagic IVR Daily  সার্ভিসটি ইতিমধ্যে চালু আছে। বন্ধ করতে, এসএমএস করুন STOP BD লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ১০টা পর্যন্ত)',
            '16303_momagic_ivr_Weekly_auto' => 'আপনার Momagic IVR Weekly  সার্ভিসটি ইতিমধ্যে চালু আছে। বন্ধ করতে, এসএমএস করুন STOP BW লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ১০টা পর্যন্ত)',
            '16303_momagic_ivr_monthly_auto' => 'আপনার Momagic IVR Monthly  সার্ভিসটি ইতিমধ্যে চালু আছে। বন্ধ করতে, এসএমএস করুন STOP BM লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ১০টা পর্যন্ত)',
        ],
    ],
    'serviceConfig' => [
        'services' => [
            '16303_momagic_ivr_daily_auto' => [
                'planId' => '9913110002',
                'subscriptionOfferId' => '9913110012',
            ],
            '16303_momagic_ivr_Weekly_auto' => [
                'planId' => '9913110002',
                'subscriptionOfferId' => '9913110013',
            ],
            '16303_momagic_ivr_monthly_auto' => [
                'planId' => '9913110002',
                'subscriptionOfferId' => '9913110014',
            ],
        ],
        '9913110012' => '16303_momagic_ivr_daily_auto',
        '9913110013' => '16303_momagic_ivr_Weekly_auto',
        '9913110014' => '16303_momagic_ivr_monthly_auto'
    ]
];
