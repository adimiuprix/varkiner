<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Varkiner — Bot Control</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-zinc-950 text-zinc-100 antialiased flex items-center justify-center p-4">

    <div class="w-full max-w-sm space-y-6">

        {{-- Header --}}
        <div class="text-center space-y-1">
            <div class="inline-flex items-center gap-2 mb-3">
                {{-- Pulse indicator --}}
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-medium text-emerald-400 uppercase tracking-widest">Bot Active</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-50">Varkiner</h1>
            <p class="text-sm text-zinc-500">Bitget USDT-Futures · {{ $pair->lavarage ?? 15 }}× Leverage</p>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl border border-zinc-800 bg-zinc-900 shadow-xl shadow-black/40 overflow-hidden">

            {{-- Card header --}}
            <div class="px-5 py-4 border-b border-zinc-800 flex items-center gap-3">
                <div class="p-2 rounded-lg bg-zinc-800">
                    <svg class="w-4 h-4 text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-zinc-100">Pair Configuration</p>
                    <p class="text-xs text-zinc-500">Active trading pair &amp; direction</p>
                </div>
            </div>

            {{-- Form --}}
            <form action="{{ route('editpair') }}" method="post" class="px-5 py-5 space-y-4">
                @csrf

                {{-- Flash messages --}}
                @if(session('success'))
                <div class="flex items-center gap-2 rounded-lg bg-emerald-500/10 border border-emerald-500/20 px-4 py-3 text-sm text-emerald-400">
                    <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="rounded-lg bg-rose-500/10 border border-rose-500/20 px-4 py-3 space-y-1">
                    @foreach($errors->all() as $error)
                    <p class="flex items-start gap-2 text-sm text-rose-400">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        {{ $error }}
                    </p>
                    @endforeach
                </div>
                @endif

                {{-- Current pair badge --}}
                @if($pair)
                <div class="flex items-center justify-between rounded-lg bg-zinc-800/60 border border-zinc-700/50 px-4 py-3">
                    <span class="text-xs text-zinc-500 uppercase tracking-wider">Current</span>
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-semibold text-sm text-zinc-100">{{ $pair->pair }}</span>
                        <span @class([
                            'text-xs font-semibold px-2 py-0.5 rounded-full uppercase',
                            'bg-emerald-500/15 text-emerald-400' => $pair->side === 'buy',
                            'bg-rose-500/15 text-rose-400'       => $pair->side === 'sell',
                        ])>{{ $pair->side }}</span>
                    </div>
                </div>
                @endif

                {{-- Pair input --}}
                <div class="space-y-1.5">
                    <label for="pair" class="block text-xs font-medium text-zinc-400 uppercase tracking-wider">
                        Symbol
                    </label>
                    <input
                        id="pair"
                        type="text"
                        name="pair"
                        value="{{ $pair->pair ?? '' }}"
                        placeholder="e.g. BTCUSDT"
                        autocomplete="off"
                        spellcheck="false"
                        class="w-full rounded-lg bg-zinc-800 border border-zinc-700 text-zinc-100 placeholder-zinc-600
                               font-mono text-sm px-4 py-2.5
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                               transition"
                    />
                </div>

                {{-- Side select --}}
                <div class="space-y-1.5">
                    <label for="side" class="block text-xs font-medium text-zinc-400 uppercase tracking-wider">
                        Direction
                    </label>
                    <select
                        id="side"
                        name="side"
                        class="w-full rounded-lg bg-zinc-800 border border-zinc-700 text-zinc-100
                               text-sm px-4 py-2.5 appearance-none
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                               transition cursor-pointer"
                    >
                        <option value="buy"  {{ ($pair->side ?? '') == 'buy'  ? 'selected' : '' }}>
                            ↑ Buy / Long
                        </option>
                        <option value="sell" {{ ($pair->side ?? '') == 'sell' ? 'selected' : '' }}>
                            ↓ Sell / Short
                        </option>
                    </select>
                </div>

                {{-- Leverage & Margin --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label for="lavarage" class="block text-xs font-medium text-zinc-400 uppercase tracking-wider">
                            Leverage
                        </label>
                        <div class="relative">
                            <input
                                id="lavarage"
                                type="number"
                                name="lavarage"
                                value="{{ $pair->lavarage ?? 15 }}"
                                min="1"
                                max="125"
                                class="w-full rounded-lg bg-zinc-800 border border-zinc-700 text-zinc-100 placeholder-zinc-600
                                       font-mono text-sm px-4 py-2.5 pr-8
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                       transition"
                            />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-zinc-500 pointer-events-none">×</span>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label for="margin" class="block text-xs font-medium text-zinc-400 uppercase tracking-wider">
                            Margin (USDT)
                        </label>
                        <div class="relative">
                            <input
                                id="margin"
                                type="number"
                                name="margin"
                                value="{{ $pair->margin ?? 1 }}"
                                min="1"
                                class="w-full rounded-lg bg-zinc-800 border border-zinc-700 text-zinc-100 placeholder-zinc-600
                                       font-mono text-sm px-4 py-2.5 pr-10
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent
                                       transition"
                            />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-zinc-500 pointer-events-none">$</span>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700
                           text-white text-sm font-semibold py-2.5 px-4
                           transition focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 focus:ring-offset-zinc-900
                           flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Save &amp; Restart Bot
                </button>

            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-zinc-700">
            Changes will restart the PM2 <span class="font-mono">bot</span> process immediately.
        </p>

    </div>

</body>

</html>
