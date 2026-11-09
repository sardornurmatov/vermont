<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = ['name','email','password','role','email_verified','verification_code_hash','verification_expires_at','email_verified_at'];
    protected $hidden = ['password','remember_token','verification_code_hash'];
    protected function casts(): array { return ['password'=>'hashed','email_verified'=>'boolean','verification_expires_at'=>'datetime','email_verified_at'=>'datetime']; }
    public function orders() { return $this->hasMany(Order::class); }
    public function deliveries() { return $this->hasMany(Order::class, 'courier_id'); }
    public function productReviews() { return $this->hasMany(ProductReview::class); }
}
