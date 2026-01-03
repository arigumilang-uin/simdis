{{-- Preview Modal Content --}}
<div class="modal-body">
    {{-- Summary --}}
    <div class="alert alert-info">
        <h5><i class="icon fas fa-info-circle"></i> Ringkasan</h5>
        Akan mencatat <strong>{{ $total_pelanggaran }} jenis pelanggaran</strong> untuk 
        <strong>{{ $total_siswa }} siswa</strong> (total <strong>{{ $total_records }} records</strong>).
    </div>

    {{-- Warnings (High Impact) --}}
    @if(count($warnings) > 0)
    <div class="alert alert-warning">
        <h5><i class="icon fas fa-exclamation-triangle"></i> Peringatan Penting</h5>
        <ul class="mb-0 pl-3">
            @foreach($warnings as $warning)
            <li>{!! $warning !!}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Infos (Additional Information) --}}
    @if(count($infos) > 0)
    <div class="alert alert-light border">
        <h5><i class="icon fas fa-lightbulb"></i> Informasi Tambahan</h5>
        <ul class="mb-0 pl-3">
            @foreach($infos as $info)
            <li>{!! $info !!}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- No Impact --}}
    @if(count($warnings) === 0 && count($infos) === 0)
    <div class="alert alert-success">
        <i class="icon fas fa-check-circle"></i> 
        Tidak ada dampak signifikan terdeteksi. Pencatatan dapat dilanjutkan.
    </div>
    @endif

    {{-- Confirmation Checkbox (if high impact) --}}
    @if($requires_confirmation)
    <div class="form-group mt-3">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="confirm-preview">
            <label class="custom-control-label" for="confirm-preview">
                <strong>Saya sudah memverifikasi bahwa pelanggaran ini benar terjadi dan memahami dampaknya</strong>
            </label>
        </div>
    </div>
    @endif
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">
        <i class="fas fa-times mr-1"></i> Batal
    </button>
    <button type="button" class="btn btn-primary" id="btn-confirm-submit" 
            @if($requires_confirmation) disabled @endif>
        <i class="fas fa-check mr-1"></i> Lanjutkan Pencatatan
    </button>
</div>

@if($requires_confirmation)
<script>
    // Enable submit button only if checkbox is checked
    $('#confirm-preview').change(function() {
        $('#btn-confirm-submit').prop('disabled', !this.checked);
    });
</script>
@endif
