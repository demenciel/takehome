@props(['items'])

@if (count($items) > 1)
    <nav aria-label="Breadcrumb" class="mx-auto max-w-6xl px-4 pt-6 text-sm text-ink-soft sm:px-6">
        <ol class="flex flex-wrap gap-2">
            @foreach ($items as $index => $item)
                <li class="flex items-center gap-2">
                    @if (! $loop->last)
                        <a href="{{ $item['url'] }}" class="hover:text-ink">{{ $item['name'] }}</a>
                        <span aria-hidden="true">/</span>
                    @else
                        <span class="text-ink" aria-current="page">{{ $item['name'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
