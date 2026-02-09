<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Welcome to PageTurner Bookstore') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Categories Section -->
            <div class="mb-8">
                <h3 class="text-2xl font-bold mb-4">Browse by Category</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($categories as $category)
                        <a href="{{ route('categories.show', $category) }}" class="bg-white p-6 rounded-lg shadow hover:shadow-md transition text-center group">
                            <span class="text-lg font-semibold text-indigo-600 group-hover:text-indigo-800">{{ $category->name }}</span>
                            <p class="text-xs text-gray-500 mt-1">{{ Str::limit($category->description, 50) }}</p>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Featured Books Section -->
            <div>
                <h3 class="text-2xl font-bold mb-4">Featured Books</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    @foreach($featuredBooks as $book)
                        <x-book-card :book="$book" />
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
