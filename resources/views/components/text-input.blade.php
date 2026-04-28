@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border border-[#85f2a1]/35 bg-white text-[#183624] shadow-sm focus:border-[#85f2a1] focus:ring-[#85f2a1]']) }}>
