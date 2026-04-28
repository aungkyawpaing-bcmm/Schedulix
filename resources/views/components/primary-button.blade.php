<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-xl border border-transparent bg-[#85f2a1] px-4 py-2 text-xs font-semibold uppercase tracking-widest text-[#183624] transition ease-in-out duration-150 hover:bg-[#6fe58d] focus:bg-[#6fe58d] focus:outline-none focus:ring-2 focus:ring-[#85f2a1] focus:ring-offset-2 active:bg-[#5fd57e]']) }}>
    {{ $slot }}
</button>
