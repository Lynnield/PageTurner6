<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Categories') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($categories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition duration-300 group">
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 mb-2">
                                {{ $category->name }}
                            </h3>
                            <p class="text-gray-600 mb-4">
                                {{ Str::limit($category->description, 100) }}
                            </p>
                            <span class="text-sm text-indigo-500 font-semibold group-hover:underline">
                                Browse {{ $category->books_count ?? '' }} Books &rarr;
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
