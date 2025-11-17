<!-- resources/views/components/admin/table.blade.php -->
<div class="overflow-x-auto">
    <table id="{{ $tableId }}" class="w-full text-left text-gray-700 display">
        <thead>
            <tr class="bg-gray-50 text-xs uppercase text-gray-500">
                @foreach($columns as $column)
                    <th class="px-6 py-3 {{ $column['class'] ?? '' }}">
                        {{ $column['label'] ?? $column['name'] ?? '' }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200"></tbody>
    </table>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#{{ $tableId }}').DataTable({
            processing: true,
            serverSide: {{ $serverSide ?? 'false' }},
            ajax: {
                url: '{{ $ajaxUrl }}',
                type: 'GET',
                dataSrc: '{{ $dataSrc ?? "data" }}'
            },
            columns: {!! json_encode($columns) !!},
            responsive: true,
            pageLength: {{ $pageLength ?? 10 }},
            language: {
                emptyTable: "Tidak ada data tersedia",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                search: "Cari:"
            }
        });
    });
</script>
@endpush