<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;

class SubCategoryTag extends Model
{
    protected $table = 'sub_category_tags';
    protected $primaryKey = 'id';
    protected $fillable = ['sub_category_id', 'sub_category_tags'];
    protected $hidden = ['created_at', 'updated_at'];

    public function getSubCategoryTagsAttribute($value)
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
}
