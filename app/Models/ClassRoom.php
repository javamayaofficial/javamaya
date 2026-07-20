<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Nama model ClassRoom karena `Class` reserved word PHP; tabel tetap `classes`. */
class ClassRoom extends Model
{
    protected $table = 'classes';
    protected $fillable = ['title', 'slug', 'class_category_id', 'description', 'cover_path', 'certificate_enabled'];
    protected $casts = ['certificate_enabled' => 'boolean'];

    public function materials()    { return $this->hasMany(Material::class, 'class_id')->orderBy('sort_order'); }
    public function category()     { return $this->belongsTo(ClassCategory::class, 'class_category_id'); }
    public function certificates() { return $this->hasMany(Certificate::class, 'class_id'); }

    public function progressFor(User $user): int
    {
        $total = $this->materials()->count();
        if ($total === 0) return 0;
        $done = MaterialCompletion::where('user_id', $user->id)
            ->whereIn('material_id', $this->materials()->pluck('id'))->count();
        return (int) floor($done / $total * 100);
    }
}
