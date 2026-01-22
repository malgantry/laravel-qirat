@extends('layouts.app')

@section('content')
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h3 class="mb-0">سجل تغذية AI</h3>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>feedback_id</th>
                    <th>user_id</th>
                    <th>action</th>
                    <th>object</th>
                    <th>created_at</th>
                    <th>meta</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $it)
                    <tr data-ai-feedback-id="{{ $it->id }}">
                        <td>{{ $it->id }}</td>
                        <td>{{ $it->feedback_id }}</td>
                        <td>{{ $it->user_id }}</td>
                        <td>{{ $it->action }}</td>
                        <td>{{ $it->object_type }}:{{ $it->object_id }}</td>
                        <td>{{ $it->created_at }}</td>
                        <td><pre style="white-space:pre-wrap">{{ json_encode($it->meta, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) }}</pre></td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="deleteAiFeedback({{ $it->id }})">حذف</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-3">
            {{ $items->links() }}
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function deleteAiFeedback(id) {
        if (!confirm('هل أنت متأكد أنك تريد حذف هذا السجل؟')) return;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch('{{ url('/admin/ai-feedbacks') }}/' + id, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(r => {
            if (r.ok) return r.json().catch(() => ({ status: 'success' }));
            throw new Error('Network');
        }).then(data => {
            const row = document.querySelector('[data-ai-feedback-id="' + id + '"]');
            if (row) {
                // animate collapse + fade
                row.style.transition = 'opacity 280ms ease, height 300ms ease, margin 300ms ease, padding 300ms ease';
                const startHeight = row.getBoundingClientRect().height + 'px';
                row.style.height = startHeight;
                // force layout
                // eslint-disable-next-line no-unused-expressions
                row.offsetHeight;
                row.style.opacity = '0';
                row.style.height = '0';
                row.style.margin = '0';
                row.style.padding = '0';
                setTimeout(() => { row.remove(); }, 320);
            }
            const toast = document.getElementById('toast-area');
            if (toast) {
                const chip = document.createElement('div');
                chip.className = 'toast-chip';
                chip.textContent = 'تم الحذف';
                toast.appendChild(chip);
                setTimeout(() => chip.remove(), 2500);
            }
        }).catch(() => {
            alert('تعذر حذف السجل. حاول مرة أخرى.');
        });
    }
</script>
@endpush
