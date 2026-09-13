@php $cm = $conferenceMessage ?? null; @endphp
<script>
    Dropzone.options.photoDropzone = {
        url: '{{ route('admin.conference-messages.storeMedia') }}',
        maxFilesize: 2,
        acceptedFiles: '.jpeg,.jpg,.png,.gif',
        maxFiles: 1,
        addRemoveLinks: true,
        headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
        params: { size: 2, width: 4096, height: 4096 },
        success: function (file, response) {
            $('form').find('input[name="photo"]').remove()
            $('form').append('<input type="hidden" name="photo" value="' + response.name + '">')
        },
        removedfile: function (file) {
            file.previewElement.remove()
            if (file.status !== 'error') {
                $('form').find('input[name="photo"]').remove()
                this.options.maxFiles = this.options.maxFiles + 1
            }
        },
        init: function () {
            @if($cm && $cm->photo)
            var file = {!! json_encode($cm->photo) !!}
            this.options.addedfile.call(this, file)
            this.options.thumbnail.call(this, file, file.url)
            file.previewElement.classList.add('dz-complete')
            $('form').append('<input type="hidden" name="photo" value="' + file.file_name + '">')
            this.options.maxFiles = this.options.maxFiles - 1
            @endif
        },
        error: function (file, response) {
            var message = ($.type(response) === 'string') ? response : response.errors.file
            file.previewElement.classList.add('dz-error')
            var _ref = file.previewElement.querySelectorAll('[data-dz-errormessage]')
            for (var _i = 0; _i < _ref.length; _i++) { _ref[_i].textContent = message }
        }
    }
</script>
