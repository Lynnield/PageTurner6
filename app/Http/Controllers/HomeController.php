<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        $featuredBooks = Book::with(['category', 'reviews'])->latest()->take(6)->get();
        $categories = Category::select(['id', 'name'])->get();

        return view('home', compact('featuredBooks', 'categories'));
    }
}
