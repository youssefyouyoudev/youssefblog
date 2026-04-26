@props(['rows'])

<div class="overflow-hidden rounded-lg border border-black/10 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-black/10 text-left text-sm">
            <thead class="bg-black text-white">
                <tr>
                    <th class="px-5 py-4 font-black">Option</th>
                    <th class="px-5 py-4 font-black">Best For</th>
                    <th class="px-5 py-4 font-black">Strength</th>
                    <th class="px-5 py-4 font-black">Watch Out</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/10">
                @foreach ($rows as $row)
                    <tr class="transition hover:bg-emerald-50/70">
                        <td class="px-5 py-4 font-black text-black">{{ $row[0] }}</td>
                        <td class="px-5 py-4 text-slate-700">{{ $row[1] }}</td>
                        <td class="px-5 py-4 text-slate-700">{{ $row[2] }}</td>
                        <td class="px-5 py-4 text-slate-700">{{ $row[3] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
