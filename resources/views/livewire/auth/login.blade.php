<div>
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Selamat Datang!</h1>
        <p class="text-gray-500 mt-2">Masuk untuk melanjutkan ke <span class="font-semibold text-indigo-600">{{ config('app.name', 'NanangStore') }}</span></p>
    </div>

    <form wire:submit.prevent="login" class="space-y-6">
        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
            <div class="mt-1">
                <input id="email" wire:model.lazy="email" type="email" autocomplete="email" required 
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-500 @enderror">
            </div>
            @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
            <div class="mt-1">
                <input id="password" wire:model.lazy="password" type="password" autocomplete="current-password" required 
                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-500 @enderror">
            </div>
            @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember" wire:model="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="remember" class="ml-2 block text-sm text-gray-900">Ingat saya</label>
            </div>
        </div>

        <div>
            <button type="submit" wire:loading.attr="disabled" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <span wire:loading.remove wire:target="login">Masuk</span>
                <span wire:loading wire:target="login">Memproses...</span>
            </button>
        </div>
    </form>

    <p class="text-center text-sm text-gray-600 mt-8">
        Belum punya akun? 
        <a href="{{ route('register') }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-500">Daftar di sini</a>
    </p>
</div>