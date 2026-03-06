<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Security Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">{{ __('Two-Factor Authentication') }}</h3>
                @if(session('success'))
                    <div class="mb-4 text-green-600">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="mb-4 text-red-600">{{ $errors->first() }}</div>
                @endif
                @if($secret && $secret->enabled_at)
                    <p class="mb-4">{{ __('Status: Enabled (Email OTP)') }}</p>
                    <form method="POST" action="{{ route('two-factor.disable') }}">
                        @csrf
                        <x-primary-button>{{ __('Disable 2FA') }}</x-primary-button>
                    </form>
                @else
                    <p class="mb-4">{{ __('Status: Disabled') }}</p>
                    <form method="POST" action="{{ route('two-factor.enable-email') }}">
                        @csrf
                        <x-primary-button>{{ __('Enable Email OTP') }}</x-primary-button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
