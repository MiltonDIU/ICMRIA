@extends('layouts.admin')
@section('content')
<div style="margin-bottom: 10px;" class="row">
    <div class="col-lg-12">
        @can('conference_message_category_create')
            <a class="btn btn-success" href="{{ route('admin.conference-message-categories.create') }}">
                <i class="fas fa-plus mr-1"></i> Add Message Category
            </a>
        @endcan
        @can('conference_message_access')
            <a class="btn btn-outline-primary ml-2" href="{{ route('admin.conference-messages.index') }}">
                <i class="fas fa-comment-dots mr-1"></i> View All Messages
            </a>
        @endcan
    </div>
</div>
<div class="card">
    <div class="card-header font-weight-bold">
        Message Categories
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable datatable-MessageCategories">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Total Messages</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr data-entry-id="{{ $category->id }}">
                            <td></td>
                            <td>{{ $category->id }}</td>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td>{{ $category->sort_order }}</td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $category->messages_count }} Messages</span>
                            </td>
                            <td>
                                @can('conference_message_category_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.conference-message-categories.edit', $category->id) }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endcan
                                @can('conference_message_category_delete')
                                    <form action="{{ route('admin.conference-message-categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@section('scripts')
@parent
<script>
    $(function () {
        let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
        @can('conference_message_category_delete')
        let deleteButton = {
            text: 'Delete Selected',
            url: "{{ route('admin.conference-message-categories.massDestroy') }}",
            className: 'btn-danger',
            action: function (e, dt, node, config) {
                var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
                    return $(entry).data('entry-id')
                });
                if (ids.length === 0) { alert('No rows selected!'); return }
                if (confirm('Are you sure?')) {
                    $.ajax({
                        headers: {'x-csrf-token': _token},
                        method: 'POST',
                        url: config.url,
                        data: { ids: ids, _method: 'DELETE' }
                    }).done(function () { location.reload() })
                }
            }
        }
        dtButtons.push(deleteButton)
        @endcan
        $.extend(true, $.fn.dataTable.defaults, { order: [[ 4, 'asc' ]], pageLength: 100 });
        $('.datatable-MessageCategories:not(.ajaxTable)').DataTable({ buttons: dtButtons })
    })
</script>
@endsection