@extends('layouts.admin')
@section('content')
<div style="margin-bottom: 10px;" class="row">
    <div class="col-lg-12">
        @can('schedule_category_create')
            <a class="btn btn-success" href="{{ route('admin.schedule-categories.create') }}">
                <i class="fas fa-plus mr-1"></i> Add Session Category
            </a>
        @endcan
        @can('schedule_access')
            <a class="btn btn-outline-primary ml-2" href="{{ route('admin.schedules.index') }}">
                <i class="far fa-clock mr-1"></i> View All Schedules
            </a>
        @endcan
    </div>
</div>

<div class="card">
    <div class="card-header font-weight-bold">
        Schedule Session Categories
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable datatable-ScheduleCategories">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Slug</th>
                        <th>Color Badge</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Total Sessions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr data-entry-id="{{ $category->id }}">
                            <td></td>
                            <td>{{ $category->id }}</td>
                            <td>
                                <strong>{{ $category->name }}</strong>
                                @if($category->description)
                                    <br><small class="text-muted">{{ Str::limit($category->description, 60) }}</small>
                                @endif
                            </td>
                            <td><code>{{ $category->slug }}</code></td>
                            <td>
                                <span class="badge text-white px-2 py-1" style="background-color: {{ $category->color ?? '#00396B' }};">
                                    {{ $category->name }}
                                </span>
                                <small class="text-muted ml-1">{{ $category->color }}</small>
                            </td>
                            <td>{{ $category->sort_order }}</td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $category->schedules_count }} Sessions</span>
                            </td>
                            <td>
                                @can('schedule_category_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.schedule-categories.edit', $category->id) }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endcan
                                @can('schedule_category_delete')
                                    <form action="{{ route('admin.schedule-categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this session category?');" style="display: inline-block;">
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
        @can('schedule_category_delete')
        let deleteButton = {
            text: 'Delete Selected',
            url: "{{ route('admin.schedule-categories.massDestroy') }}",
            className: 'btn-danger',
            action: function (e, dt, node, config) {
                var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
                    return $(entry).data('entry-id')
                });
                if (ids.length === 0) { alert('No rows selected!'); return }
                if (confirm('Are you sure you want to delete selected categories?')) {
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
        $.extend(true, $.fn.dataTable.defaults, { order: [[ 5, 'asc' ]], pageLength: 50 });
        $('.datatable-ScheduleCategories:not(.ajaxTable)').DataTable({ buttons: dtButtons })
    })
</script>
@endsection
