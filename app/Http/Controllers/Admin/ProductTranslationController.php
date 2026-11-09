<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductTranslator;
use Illuminate\Http\Request;
use RuntimeException;

class ProductTranslationController extends Controller
{
    public function __invoke(Request $request, ProductTranslator $translator)
    {
        $data = $request->validate([
            'name' => 'required|string|max:180',
            'description' => 'nullable|string|max:2000',
        ]);

        try {
            $translations = [];
            foreach (['ru', 'en'] as $locale) {
                $translations['name_'.$locale] = $translator->translate($data['name'], $locale);
                if (filled($data['description'] ?? null)) {
                    $translations['description_'.$locale] = $translator->translate($data['description'], $locale);
                }
            }

            return response()->json($translations);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => __($exception->getMessage())], 422);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => __('Tarjima vaqtida xatolik yuz berdi. Birozdan keyin qayta urinib ko‘ring.')], 502);
        }
    }
}
