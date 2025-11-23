<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-700 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider hover:from-primary-700 hover:to-primary-800 focus:from-primary-700 focus:to-primary-800 active:from-primary-800 active:to-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200']) }}>
    {{ $slot }}
</button>
