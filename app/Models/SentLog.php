<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SentLog extends Model {
    public $timestamps = false;
    protected $fillable = ['tenant_id','invoice_id','channel','recipient','status','error_message','sent_at'];
    protected $casts = ['sent_at' => 'datetime'];
    public function invoice() { return $this->belongsTo(Invoice::class); }
}
