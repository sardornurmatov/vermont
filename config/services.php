<?php

return ['telegram' => ['bot_token' => env('TELEGRAM_BOT_TOKEN'), 'chat_id' => env('TELEGRAM_CHAT_ID'), 'channel_url' => env('TELEGRAM_CHANNEL_URL')], 'payments' => ['card_number' => env('PAYMENT_CARD_NUMBER', '8600 0000 0000 0000'), 'card_holder' => env('PAYMENT_CARD_HOLDER', 'VERMONTSHOP')], 'delivery' => ['fee' => (int) env('DELIVERY_FEE', 0)], 'google_translate' => ['key' => env('GOOGLE_TRANSLATE_API_KEY')]];
