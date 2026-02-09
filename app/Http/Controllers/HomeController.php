<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredBooks = Book::latest()->take(6)->get();
        $categories = Category::all();

        return view('home', compact('featuredBooks', 'categories'));
    }
}
