<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All Books') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Search & Filter -->
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <form method="GET" action="{{ route('books.index') }}" class="flex flex-col md:flex-row gap-4">
                    <input type="text" name="search" placeholder="Search by title or author..." value="{{ request('search') }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 flex-1">
                    
                    <select name="category" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                        Filter
                    </button>
                    
                    @if(request()->filled('search') || request()->filled('category'))
                        <a href="{{ route('books.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition flex items-center justify-center">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Books Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse($books as $book)
                    <x-book-card :book="$book" />
                @empty
                    <div class="col-span-full text-center py-12 text-gray-500">
                        No books found matching your criteria.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $books->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
