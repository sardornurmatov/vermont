<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['order_number','user_id','phone','address','total','status','payment_method','receipt_path','admin_note','delivery_fee','delivery_note','delivery_status','courier_id','estimated_delivery_at'];
    protected function casts(): array { return ['estimated_delivery_at' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function courier() { return $this->belongsTo(User::class, 'courier_id'); }
}
