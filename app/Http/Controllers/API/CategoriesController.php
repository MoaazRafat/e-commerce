<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function navbarcategories()
    {
        $categories = CategoryResource::collection(Category::all());
        return $categories;
    }
}
