@props(['book'])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-md h-full flex flex-col hover:shadow-md transition-shadow duration-300">
    <a href="{{ route('books.show', $book) }}" class="block flex-shrink-0 relative aspect-[2/3] w-full overflow-hidden bg-indigo-50 flex items-center justify-center p-8">
        <img src="{{ $book->cover_image_url }}" alt="{{ $book->title }}" class="object-contain w-full h-full max-h-full opacity-80" onerror="this.src='https://placehold.co/400x600?text=No+Cover'">
    </a>
    <div class="p-3 flex flex-col flex-1">
        <div class="mb-1">
            <span class="text-xs font-semibold tracking-wide uppercase text-indigo-600">{{ $book->category->name_en ?? $book->category->name }}</span>
        </div>
        <h3 class="text-base font-semibold text-gray-900 leading-snug mb-1">
            <a href="{{ route('books.show', $book) }}" class="hover:underline truncate">
                {{ $book->title_en ?? $book->title }}
            </a>
        </h3>
        <p class="text-xs text-gray-600 mb-2 truncate">{{ $book->author }}</p>
        
        <div class="mt-auto pt-3 flex items-center justify-between">
            <span class="text-base font-bold text-gray-900">${{ number_format($book->price, 2) }}</span>
            <div class="flex items-center">
                 <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                 <span class="ml-1 text-xs text-gray-600">{{ number_format($book->average_rating, 1) }}</span>
            </div>
        </div>
    </div>
</div>
