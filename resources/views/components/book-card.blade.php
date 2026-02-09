@props(['book'])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg h-full flex flex-col hover:shadow-md transition-shadow duration-300">
    <a href="{{ route('books.show', $book) }}" class="block flex-shrink-0 relative aspect-[2/3] w-full overflow-hidden bg-gray-200">
        @if($book->cover_image)
            <img src="{{ Storage::url($book->cover_image) }}" alt="{{ $book->title }}" class="object-cover w-full h-full">
        @else
            <div class="flex items-center justify-center w-full h-full text-gray-400 bg-gray-100">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
        @endif
    </a>
    <div class="p-4 flex flex-col flex-1">
        <div class="mb-2">
            <span class="text-xs font-semibold tracking-wide uppercase text-indigo-600">{{ $book->category->name }}</span>
        </div>
        <h3 class="text-lg font-bold text-gray-900 leading-tight mb-1">
            <a href="{{ route('books.show', $book) }}" class="hover:underline">
                {{ $book->title }}
            </a>
        </h3>
        <p class="text-sm text-gray-600 mb-2">{{ $book->author }}</p>
        
        <div class="mt-auto pt-4 flex items-center justify-between">
            <span class="text-xl font-bold text-gray-900">${{ number_format($book->price, 2) }}</span>
            <div class="flex items-center">
                 <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                 <span class="ml-1 text-sm text-gray-600">{{ number_format($book->average_rating, 1) }}</span>
            </div>
        </div>
    </div>
</div>
