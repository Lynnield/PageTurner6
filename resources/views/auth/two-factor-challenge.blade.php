<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Enter the 6-digit code sent to your email to continue.') }}
    </div>

    <form method="POST" action="{{ route('two-factor.verify') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Code')" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" inputmode="numeric" required autofocus />
            @error('code')
                <div class="text-sm text-red-600 mt-2">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Verify') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
