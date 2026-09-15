@extends('layouts.admin')
@section('content')
@can('speaker_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.speakers.create") }}">
                {{ trans('global.add') }} {{ trans('cruds.speaker.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header font-weight-bold">
        {{ trans('cruds.speaker.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Speaker">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            Photo
                        </th>
                        <th>
                            {{ trans('cruds.speaker.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.speaker.fields.speaker_type_id') }}
                        </th>
                        <th>
                            Track / Focus Area
                        </th>
                        <th>
                            Affiliation & Country
                        </th>
                        <th>
                            {{ trans('cruds.speaker.fields.serial') }}
                        </th>
                        <th>
                            {{ trans('cruds.speaker.fields.show_home') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($speakers as $key => $speaker)
                        <tr data-entry-id="{{ $speaker->id }}">
                            <td>

                            </td>
                            <td class="text-center">
                                <img src="{{ $speaker->photo ? $speaker->photo->getUrl() : asset('img/default-speaker.jpg') }}" 
                                     alt="{{ $speaker->name }}" 
                                     width="42" height="42" 
                                     style="object-fit: cover; border-radius: 50%; border: 1px solid #dee2e6;">
                            </td>
                            <td>
                                <strong>{{ $speaker->name ?? '' }}</strong>
                                @if($speaker->slug)
                                    <br><small class="text-muted">{{ $speaker->slug }}</small>
                                @endif
                            </td>
                            <td>
                                @if($speaker->speakerType)
                                    <span class="badge badge-primary">{{ $speaker->speakerType->title }}</span>
                                @else
                                    <span class="badge badge-secondary">Not Assigned</span>
                                @endif
                            </td>
                            <td>
                                @if($speaker->track)
                                    <span class="badge badge-info">{{ $speaker->track->name }}</span>
                                @endif
                                @if($speaker->focus_area)
                                    <br><small class="text-muted">{{ $speaker->focus_area }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $speaker->affiliation ?? '' }}
                                @if($speaker->country)
                                    <br><span class="badge badge-light border">{{ $speaker->country }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $speaker->serial ?? '' }}
                            </td>
                            <td class="text-center">
                                @if($speaker->show_home == 1)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                @can('speaker_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.speakers.show', $speaker->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('speaker_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.speakers.edit', $speaker->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('speaker_delete')
                                    <form action="{{ route('admin.speakers.destroy', $speaker->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
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
@can('speaker_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.speakers.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    order: [[ 6, 'asc' ]],
    pageLength: 25,
  });
  $('.datatable-Speaker:not(.ajaxTable)').DataTable({ buttons: dtButtons })
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection
