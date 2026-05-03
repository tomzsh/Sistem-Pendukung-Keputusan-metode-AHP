<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 antialiased dark:bg-neutral-950">
        <div class="relative flex min-h-svh flex-col items-center justify-center gap-6 overflow-hidden p-4 md:p-10">
            <div class="absolute inset-0 bg-[linear-gradient(rgba(15,118,110,.08)_1px,transparent_1px),linear-gradient(90deg,rgba(37,99,235,.07)_1px,transparent_1px)] bg-[size:42px_42px] dark:bg-[linear-gradient(rgba(45,212,191,.08)_1px,transparent_1px),linear-gradient(90deg,rgba(96,165,250,.07)_1px,transparent_1px)]"></div>
            <div class="absolute inset-0 bg-linear-to-b from-white/90 via-zinc-50/90 to-zinc-50 dark:from-neutral-950/80 dark:via-neutral-950/92 dark:to-neutral-950"></div>

            <div class="relative flex w-full max-w-md flex-col gap-5">
                <a href="{{ route('home') }}" class="flex items-center justify-center gap-3 font-semibold text-zinc-900 dark:text-white" wire:navigate>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg border border-teal-200 bg-white shadow-sm dark:border-teal-800 dark:bg-neutral-900">
                        <x-app-logo-icon class="size-8 fill-current text-teal-700 dark:text-teal-300" />
                    </span>
                    <span>{{ config('app.name', 'SPK AHP') }}</span>
                </a>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
