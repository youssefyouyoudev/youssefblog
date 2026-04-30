@props(['rows'])

<div class="overflow-hidden rounded-2xl border border-[var(--border)] bg-[var(--surface)] shadow-soft">
    <div class="scrollbar-soft overflow-x-auto">
        <table class="min-w-[720px] divide-y divide-[var(--border)] text-left text-sm">
            <thead class="bg-[var(--text)] text-[var(--bg)]">
                <tr>
                    <th class="px-5 py-4 font-black">Option</th>
                    <th class="px-5 py-4 font-black">Best For</th>
                    <th class="px-5 py-4 font-black">Strength</th>
                    <th class="px-5 py-4 font-black">Watch Out</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border)]">
                @foreach ($rows as $row)
                    <tr class="transition hover:bg-[var(--accent-soft)]">
                        <td class="px-5 py-4 font-black text-[var(--text)]">{{ $row[0] }}</td>
                        <td class="px-5 py-4 text-[var(--muted)]">{{ $row[1] }}</td>
                        <td class="px-5 py-4 text-[var(--muted)]">{{ $row[2] }}</td>
                        <td class="px-5 py-4 text-[var(--muted)]">{{ $row[3] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
