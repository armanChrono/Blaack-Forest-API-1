<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;

class ProductTag extends Model
{
    protected $table = 'product_tags';
    protected $primaryKey = 'product_tag_id';
    protected $fillable = ['product_id', 'product_tags'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $appends = ['tag_name'];

    public function getProductTagsAttribute($value)
    {
        $tags = [];
        if( strpos($value, ',') !== false ) {
            foreach(explode(',', $value) as $tagId) {
                array_push($tags, ['id' => (int)$tagId, 'itemName' => Tag::select('tag_name')->find($tagId)->tag_name]);
            }
        } elseif ($value) {
            array_push($tags, ['id' => (int)$value, 'itemName' => Tag::select('tag_name')->find($value)->tag_name]);
        }
        return $tags;
    }

    public function getTagNameAttribute() {
        $name = [];
        foreach($this->product_tags as $tag) {
            array_push($name, $tag['itemName']);
        }
        return $name;
    }
}
