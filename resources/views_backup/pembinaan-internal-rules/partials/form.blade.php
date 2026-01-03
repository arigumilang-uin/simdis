<!-- Range Poin -->
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="poin_min">Poin Minimum <span class="text-danger">*</span></label>
            <input type="number" 
                   class="form-control @error('poin_min') is-invalid @enderror" 
                   id="poin_min" 
                   name="poin_min" 
                   value="{{ old('poin_min', $rule->poin_min ?? '') }}" 
                   min="0"
                   required>
            @error('poin_min')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Batas bawah range poin (contoh: 0, 55, 105)</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="poin_max">Poin Maximum</label>
            <input type="number" 
                   class="form-control @error('poin_max') is-invalid @enderror" 
                   id="poin_max" 
                   name="poin_max" 
                   value="{{ old('poin_max', $rule->poin_max ?? '') }}" 
                   min="0">
            @error('poin_max')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Batas atas range poin. Kosongkan untuk open-ended (contoh: 50, 100, 300)</small>
        </div>
    </div>
</div>

<!-- Pembina Roles -->
<div class="form-group">
    <label>Pembina yang Terlibat <span class="text-danger">*</span></label>
    <div class="border rounded p-3">
        @php
            $availableRoles = ['Wali Kelas', 'Kaprodi', 'Waka Kesiswaan', 'Kepala Sekolah'];
            $selectedRoles = old('pembina_roles', $rule->pembina_roles ?? []);
        @endphp
        
        @foreach($availableRoles as $role)
        <div class="custom-control custom-checkbox">
            <input type="checkbox" 
                   class="custom-control-input" 
                   id="pembina_{{ str_replace(' ', '_', $role) }}" 
                   name="pembina_roles[]" 
                   value="{{ $role }}"
                   {{ in_array($role, $selectedRoles) ? 'checked' : '' }}>
            <label class="custom-control-label" for="pembina_{{ str_replace(' ', '_', $role) }}">
                {{ $role }}
            </label>
        </div>
        @endforeach
    </div>
    @error('pembina_roles')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Pilih minimal 1 pembina yang akan terlibat dalam pembinaan</small>
</div>

<!-- Keterangan -->
<div class="form-group">
    <label for="keterangan">Keterangan <span class="text-danger">*</span></label>
    <textarea class="form-control @error('keterangan') is-invalid @enderror" 
              id="keterangan" 
              name="keterangan" 
              rows="3" 
              maxlength="500"
              required>{{ old('keterangan', $rule->keterangan ?? '') }}</textarea>
    @error('keterangan')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Deskripsi jenis pembinaan (contoh: Pembinaan ringan, konseling)</small>
</div>

<!-- Display Order -->
<div class="form-group">
    <label for="display_order">Urutan Tampilan</label>
    <input type="number" 
           class="form-control @error('display_order') is-invalid @enderror" 
           id="display_order" 
           name="display_order" 
           value="{{ old('display_order', $rule->display_order ?? '') }}" 
           min="1">
    @error('display_order')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Kosongkan untuk otomatis diurutkan di akhir</small>
</div>

<!-- Example Box -->
<div class="alert alert-info">
    <strong><i class="fas fa-lightbulb"></i> Contoh:</strong>
    <ul class="mb-0 mt-2">
        <li><strong>0-50 poin:</strong> Wali Kelas → Pembinaan ringan, konseling</li>
        <li><strong>55-100 poin:</strong> Wali Kelas + Kaprodi → Pembinaan sedang, monitoring ketat</li>
        <li><strong>105-300 poin:</strong> Wali Kelas + Kaprodi + Waka → Pembinaan intensif, evaluasi berkala</li>
        <li><strong>305+ poin:</strong> Semua pembina → Pembinaan kritis, pertemuan dengan orang tua</li>
    </ul>
</div>
