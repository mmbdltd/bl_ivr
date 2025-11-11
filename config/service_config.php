<?php
return [
    'activationKeywords' => [
        'start su' => ['offerId' => '9913110029', 'serviceName' => 'sports_update_daily'],
        'start lt' => ['offerId' => '9913110030', 'serviceName' => 'love_tips_daily'],
        'start fq' => ['offerId' => '9913110031', 'serviceName' => 'friendship_quotes_daily'],
        'start ca' => ['offerId' => '9913110032', 'serviceName' => 'celebrity_alerts_daily'],
        'start cm' => ['offerId' => '9913110033', 'serviceName' => 'comics_alerts_daily'],
        'start ha' => ['offerId' => '9913110034', 'serviceName' => 'horoscope_alerts_daily'],
    ],
    'deactivationKeywords' => [
        'stop su' => ['offerId' => '9913110029', 'serviceName' => 'sports_update_daily'],
        'stop lt' => ['offerId' => '9913110030', 'serviceName' => 'love_tips_daily'],
        'stop fq' => ['offerId' => '9913110031', 'serviceName' => 'friendship_quotes_daily'],
        'stop ca' => ['offerId' => '9913110032', 'serviceName' => 'celebrity_alerts_daily'],
        'stop cm' => ['offerId' => '9913110033', 'serviceName' => 'comics_alerts_daily'],
        'stop ha' => ['offerId' => '9913110034', 'serviceName' => 'horoscope_alerts_daily'],
    ],
    'serviceKeywordMap' => [
        'sports_update_daily' => 'SPORTS',
        'love_tips_daily' => 'LOVE',
        'friendship_quotes_daily' => 'FRIENDSHIP',
        'celebrity_alerts_daily' => 'CELEBRITY',
        'comics_alerts_daily' => 'COMICS',
        'horoscope_alerts_daily' => 'HOROSCOPE',
    ],
    'smsTemplates' => [
        // Low balance (parking) ACTIVATION_PARKING(sub2)
        'ACTIVATION_PARKING' => [
            'sports_update_daily' => 'দুঃখিত! আপনার ব্যালেন্স কম থাকায় এই মুহুর্তে Sports Update Daily সার্ভিসটি চালু করা সম্ভব হয় নি। দয়া করে রিচার্জ করুন এবং যথেষ্ট পরিমাণ ব্যালেন্স রেখে পুনরায় চেষ্টা করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'love_tips_daily' => 'দুঃখিত! আপনার ব্যালেন্স কম থাকায় এই মুহুর্তে Love tips daily সার্ভিসটি চালু করা সম্ভব হয় নি। দয়া করে রিচার্জ করুন এবং যথেষ্ট পরিমাণ ব্যালেন্স রেখে পুনরায় চেষ্টা করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'friendship_quotes_daily' => 'দুঃখিত! আপনার ব্যালেন্স কম থাকায় এই মুহুর্তে Friendship quotes daily সার্ভিসটি চালু করা সম্ভব হয় নি। দয়া করে রিচার্জ করুন এবং যথেষ্ট পরিমাণ ব্যালেন্স রেখে পুনরায় চেষ্টা করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'celebrity_alerts_daily' => 'দুঃখিত! আপনার ব্যালেন্স কম থাকায় এই মুহুর্তে Celebrity alerts daily সার্ভিসটি চালু করা সম্ভব হয় নি। দয়া করে রিচার্জ করুন এবং যথেষ্ট পরিমাণ ব্যালেন্স রেখে পুনরায় চেষ্টা করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'comics_alerts_daily' => 'দুঃখিত! আপনার ব্যালেন্স কম থাকায় এই মুহুর্তে Comics alerts daily সার্ভিসটি চালু করা সম্ভব হয় নি। দয়া করে রিচার্জ করুন এবং যথেষ্ট পরিমাণ ব্যালেন্স রেখে পুনরায় চেষ্টা করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'horoscope_alerts_daily' => 'দুঃখিত! আপনার ব্যালেন্স কম থাকায় এই মুহুর্তে Horoscope alerts daily সার্ভিসটি চালু করা সম্ভব হয় নি। দয়া করে রিচার্জ করুন এবং যথেষ্ট পরিমাণ ব্যালেন্স রেখে পুনরায় চেষ্টা করুন। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
        ],
        // Activation Success ACTIVE (Sub1)
        'ACTIVE' => [
            'sports_update_daily' => 'Sports Update Daily সার্ভিসটি সফলভাবে চালু হয়েছে। চার্জ: 2.78 টাকা/Daily (ট্যাক্স সহ)। অটো-রিনিউ প্রযোজ্য। বন্ধ করতে এসএমএস করুন STOP SU লিখে 16303 নাম্বারে। পরবর্তী অটোরিনিউ: {date}।  হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'love_tips_daily' => 'Love tips daily সার্ভিসটি সফলভাবে চালু হয়েছে। চার্জ: 2.78 টাকা/Daily (ট্যাক্স সহ)। অটো-রিনিউ প্রযোজ্য। বন্ধ করতে এসএমএস করুন STOP LT লিখে 16303 নাম্বারে। পরবর্তী অটোরিনিউ: {date}।  হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'friendship_quotes_daily' => 'Friendship quotes daily সার্ভিসটি সফলভাবে চালু হয়েছে। চার্জ: 2.78 টাকা/Daily (ট্যাক্স সহ)। অটো-রিনিউ প্রযোজ্য। বন্ধ করতে এসএমএস করুন STOP FQ লিখে 16303 নাম্বারে। পরবর্তী অটোরিনিউ: {date}।  হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'celebrity_alerts_daily' => 'Celebrity alerts daily সার্ভিসটি সফলভাবে চালু হয়েছে। চার্জ: 2.78 টাকা/Daily (ট্যাক্স সহ)। অটো-রিনিউ প্রযোজ্য। বন্ধ করতে এসএমএস করুন STOP CA লিখে 16303 নাম্বারে। পরবর্তী অটোরিনিউ: {date}।  হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'comics_alerts_daily' => 'Comics alerts daily সার্ভিসটি সফলভাবে চালু হয়েছে। চার্জ: 2.78 টাকা/Daily (ট্যাক্স সহ)। অটো-রিনিউ প্রযোজ্য। বন্ধ করতে এসএমএস করুন STOP CM লিখে 16303 নাম্বারে। পরবর্তী অটোরিনিউ: {date}।  হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'horoscope_alerts_daily' => 'Horoscope alerts daily সার্ভিসটি সফলভাবে চালু হয়েছে। চার্জ: 2.78 টাকা/Daily (ট্যাক্স সহ)। অটো-রিনিউ প্রযোজ্য। বন্ধ করতে এসএমএস করুন STOP HA লিখে 16303 নাম্বারে। পরবর্তী অটোরিনিউ: {date}।  হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
        ],
        // Deactivate Success SUSPEND (SUB3) or DEACTIVE (UNSUB1)
        'DEACTIVATE' => [
            'sports_update_daily' => 'Sports Update Daily সার্ভিসটি সফলভাবে বন্ধ হয়েছে। পুনরায় চালু করতে এসএমএস করুন START SU লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'love_tips_daily' => 'Love tips daily সার্ভিসটি সফলভাবে বন্ধ হয়েছে। পুনরায় চালু করতে এসএমএস করুন START LT লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'friendship_quotes_daily' => 'Friendship quotes daily সার্ভিসটি সফলভাবে বন্ধ হয়েছে। পুনরায় চালু করতে এসএমএস করুন START FQ লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'celebrity_alerts_daily' => 'Celebrity alerts daily সার্ভিসটি সফলভাবে বন্ধ হয়েছে। পুনরায় চালু করতে এসএমএস করুন START CA লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'comics_alerts_daily' => 'Comics alerts daily সার্ভিসটি সফলভাবে বন্ধ হয়েছে। পুনরায় চালু করতে এসএমএস করুন START CM লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'horoscope_alerts_daily' => 'Horoscope alerts daily সার্ভিসটি সফলভাবে বন্ধ হয়েছে। পুনরায় চালু করতে এসএমএস করুন START HA লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
        ],
        'ALREADY_SUBSCRIBED' => [
            'sports_update_daily' => 'আপনার Sports Update Daily  সার্ভিসটি ইতিমধ্যে চালু আছে। বন্ধ করতে, এসএমএস করুন STOP SU লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'love_tips_daily' => 'আপনার Love Tips Daily  সার্ভিসটি ইতিমধ্যে চালু আছে। বন্ধ করতে, এসএমএস করুন STOP LT লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'friendship_quotes_daily' => 'আপনার Friendship quotes daily  সার্ভিসটি ইতিমধ্যে চালু আছে। বন্ধ করতে, এসএমএস করুন STOP FQ লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'celebrity_alerts_daily' => 'আপনার Celebrity alerts daily  সার্ভিসটি ইতিমধ্যে চালু আছে। বন্ধ করতে, এসএমএস করুন STOP CA লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'comics_alerts_daily' => 'আপনার Comics alerts daily সার্ভিসটি ইতিমধ্যে চালু আছে। বন্ধ করতে, এসএমএস করুন STOP CM লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
            'horoscope_alerts_daily' => 'আপনার Horoscope alerts daily সার্ভিসটি ইতিমধ্যে চালু আছে। বন্ধ করতে, এসএমএস করুন STOP HA লিখে 16303 নাম্বারে। হেল্পলাইন: 09611016303 (সকাল ১০টা থেকে রাত ৮টা পর্যন্ত)',
        ],
    ],
    'serviceConfig' => [
        'services' => [
            'sports_update_daily' => [
                'planId' => '9913110002',
                'subscriptionOfferId' => '9913110029',
            ],
            'love_tips_daily' => [
                'planId' => '9913110002',
                'subscriptionOfferId' => '9913110030',
            ],
            'friendship_quotes_daily' => [
                'planId' => '9913110002',
                'subscriptionOfferId' => '9913110031',
            ],
            'celebrity_alerts_daily' => [
                'planId' => '9913110002',
                'subscriptionOfferId' => '9913110032',
            ],
            'comics_alerts_daily' => [
                'planId' => '9913110002',
                'subscriptionOfferId' => '9913110033',
            ],
            'horoscope_alerts_daily' => [
                'planId' => '9913110002',
                'subscriptionOfferId' => '9913110034',
            ],
        ],
        '9913110029' => 'sports_update_daily',
        '9913110030' => 'love_tips_daily',
        '9913110031' => 'friendship_quotes_daily',
        '9913110032' => 'celebrity_alerts_daily',
        '9913110033' => 'comics_alerts_daily',
        '9913110034' => 'horoscope_alerts_daily'
    ]
];
