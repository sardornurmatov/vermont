<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name','slug','category','gender','description','translations','price','old_price','image_url','colors','sizes','stock','featured','active'];
    protected function casts(): array { return ['colors'=>'array','sizes'=>'array','translations'=>'array','featured'=>'boolean','active'=>'boolean']; }
    public function translated(string $field): string
    {
        if ($field === 'category' && $this->categoryRecord) {
            return $this->categoryRecord->translatedName();
        }

        $locale=app()->getLocale();
        $translated = data_get($this->translations, $locale.'.'.$field);
        return is_string($translated) && trim($translated) !== ''
            ? $translated
            : (string) $this->getAttribute($field);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function categoryRecord()
    {
        return $this->hasOne(Category::class, 'name', 'category');
    }
}
