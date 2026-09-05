<x-layouts.app>
    <section class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <div class="flex items-center justify-between gap-4">
            <h1 class="font-serif text-3xl">Admin</h1>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn-secondary">{{ __('common.log_out') }}</button>
            </form>
        </div>

        <p class="mt-3 text-ink-soft">Last 30 days. Exact salaries are never stored.</p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-line bg-card p-4">
                <p class="text-sm text-ink-soft">Page views</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($pageViews) }}</p>
            </div>
            <div class="rounded-xl border border-line bg-card p-4">
                <p class="text-sm text-ink-soft">Calculations started</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($started) }}</p>
            </div>
            <div class="rounded-xl border border-line bg-card p-4">
                <p class="text-sm text-ink-soft">Calculations completed</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($completed) }}</p>
            </div>
            <div class="rounded-xl border border-line bg-card p-4">
                <p class="text-sm text-ink-soft">Completion rate</p>
                <p class="mt-2 text-3xl font-semibold">{{ $completionRate }}%</p>
            </div>
        </div>

        <div class="mt-10 grid gap-8 lg:grid-cols-2">
            <section>
                <h2 class="font-serif text-2xl">Popular provinces</h2>
                <ul class="mt-4 space-y-2">
                    @forelse ($provinces as $row)
                        <li class="flex justify-between border-b border-line py-2"><span>{{ $row['label'] }}</span><span>{{ $row['total'] }}</span></li>
                    @empty
                        <li class="text-ink-soft">No completed calculations yet.</li>
                    @endforelse
                </ul>
            </section>
            <section>
                <h2 class="font-serif text-2xl">Salary ranges</h2>
                <ul class="mt-4 space-y-2">
                    @forelse ($salaryRanges as $row)
                        <li class="flex justify-between border-b border-line py-2"><span>{{ $row->salary_range }}</span><span>{{ $row->total }}</span></li>
                    @empty
                        <li class="text-ink-soft">No range data yet.</li>
                    @endforelse
                </ul>
            </section>
            <section>
                <h2 class="font-serif text-2xl">Pay frequencies</h2>
                <ul class="mt-4 space-y-2">
                    @forelse ($frequencies as $row)
                        <li class="flex justify-between border-b border-line py-2"><span>{{ $row->frequency }}</span><span>{{ $row->total }}</span></li>
                    @empty
                        <li class="text-ink-soft">No frequency data yet.</li>
                    @endforelse
                </ul>
            </section>
            <section>
                <h2 class="font-serif text-2xl">Top pages</h2>
                <ul class="mt-4 space-y-2">
                    @forelse ($topPages as $row)
                        <li class="flex justify-between gap-4 border-b border-line py-2"><span>{{ $row->path }}</span><span>{{ $row->total }}</span></li>
                    @empty
                        <li class="text-ink-soft">No page views yet.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <section class="mt-10">
            <h2 class="font-serif text-2xl">Tool pages</h2>
            <ul class="mt-4 space-y-2">
                @foreach ($toolPages as $page)
                    <li class="flex justify-between border-b border-line py-2">
                        <span>{{ $page->name }}</span>
                        <span class="text-ink-soft">{{ $page->is_published ? 'Published' : 'Draft' }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </section>
</x-layouts.app>
