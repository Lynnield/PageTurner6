<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable;

class Category extends Model implements Auditable
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $fillable = ['name', 'description'];

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
