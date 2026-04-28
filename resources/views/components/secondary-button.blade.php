<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center rounded-xl border border-[#85f2a1]/40 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-[#183624] shadow-sm transition ease-in-out duration-150 hover:bg-[#d5f0dc]/50 focus:outline-none focus:ring-2 focus:ring-[#85f2a1] focus:ring-offset-2 disabled:opacity-25']) }}>
    {{ $slot }}
</button>
