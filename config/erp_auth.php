<?php

return [
    'otp' => [
        'bypass_numbers' => array_values(array_filter(array_map(
            static fn (string $mobile): string => trim($mobile),
            explode(',', env('OTP_BYPASS_NUMBERS', ''))
        ))),
        'bypass_code' => env('OTP_BYPASS_CODE', '123456'),
        'debug_code' => env('OTP_DEBUG_CODE', '123456'),
    ],
];
