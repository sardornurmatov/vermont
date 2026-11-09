<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'translations'];

    protected function casts(): array
    {
        return ['translations' => 'array'];
    }

    public function translatedName(): string
    {
        $translation = data_get($this->translations, app()->getLocale());
        return is_string($translation) && trim($translation) !== '' ? $translation : __($this->name);
    }
}
