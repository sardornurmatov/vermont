<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ProductTranslator
{
    public function translate(string $text, string $target): string
    {
        $key = (string) config('services.google_translate.key');
        if ($key === '') {
            throw new RuntimeException('GOOGLE_TRANSLATE_API_KEY sozlanmagan.');
        }

        $response = Http::asForm()
            ->timeout(15)
            ->post('https://translation.googleapis.com/language/translate/v2?key='.urlencode($key), [
                'q' => $text,
                'target' => $target,
                'format' => 'text',
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('Tarjima xizmati javob bermadi. API kaliti va billing sozlamalarini tekshiring.');
        }

        $translated = $response->json('data.translations.0.translatedText');
        if (!is_string($translated) || trim($translated) === '') {
            throw new RuntimeException('Tarjima xizmati bo‘sh javob qaytardi.');
        }

        return html_entity_decode($translated, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
