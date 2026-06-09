<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#1c9262] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#157a52] focus:bg-[#157a52] active:bg-[#0f5e3e] focus:outline-none focus:ring-2 focus:ring-[#1c9262] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
