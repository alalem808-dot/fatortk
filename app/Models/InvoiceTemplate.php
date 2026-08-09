<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class InvoiceTemplate extends Model {
    protected $fillable = ['tenant_id','name','is_default','header_html','footer_html','css_styles','primary_color','secondary_color','font_family','show_logo','show_tax','show_discount','show_notes'];
    protected $casts = ['is_default'=>'boolean','show_logo'=>'boolean','show_tax'=>'boolean','show_discount'=>'boolean','show_notes'=>'boolean'];
    public function tenant() { return $this->belongsTo(Tenant::class); }
}
