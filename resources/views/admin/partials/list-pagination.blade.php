{{-- "Showing x–y of z" and page links for a paginated paper list. Expects: $paginator. --}}
@if($paginator->total() > 0)
    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</small>
        <div class="mb-n3">{{ $paginator->links('pagination::bootstrap-4') }}</div>
    </div>
@endif
