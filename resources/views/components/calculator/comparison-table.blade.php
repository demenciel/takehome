@props(['rows', 'caption' => 'Before and after comparison'])

<div class="mt-6 overflow-x-auto">
    <table class="data-table min-w-[32rem]">
        <caption class="sr-only">{{ $caption }}</caption>
        <thead>
            <tr>
                <th scope="col">Metric</th>
                <th scope="col" class="text-right">Before</th>
                <th scope="col" class="text-right">After</th>
                <th scope="col" class="text-right">Difference</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <th scope="row">{{ $row['label'] }}</th>
                    <td class="text-right tabular-nums">{{ $row['before'] }}</td>
                    <td class="text-right tabular-nums">{{ $row['after'] }}</td>
                    <td class="text-right tabular-nums font-semibold">{{ $row['difference'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
