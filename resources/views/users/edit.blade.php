@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('page-header')
    <x-page-header 
        title="Edit Pengguna" 
        subtitle="Perbarui data pengguna."
    />
@endsection

@section('content')
<div class="max-w-3xl">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Edit Pengguna</h3>
        </div>
        <div class="card-body">
            {{-- Role mapping from PHP to JS --}}
            @php
                $roleMap = [];
                foreach($roles ?? [] as $role) {
                    $roleMap[$role->id] = strtolower($role->nama_role);
                }
            @endphp
            
            <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6"
                  x-data="{ 
                      roleId: '{{ old('role_id', $user->role_id) }}',
                      roleMap: {{ json_encode($roleMap) }},
                      
                      getRoleName() {
                          return this.roleMap[this.roleId] || '';
                      },
                      needsNipNuptk() {
                          const roles = ['guru', 'waka kesiswaan', 'waka kurikulum', 'waka sarana', 'operator sekolah', 'wali kelas', 'kaprodi', 'kepala sekolah'];
                          const name = this.getRoleName();
                          return roles.some(r => name.includes(r));
                      },
                      isWaliKelas() {
                          return this.getRoleName().includes('wali kelas');
                      },
                      isKaprodi() {
                          return this.getRoleName().includes('kaprodi');
                      },
                      isWaliMurid() {
                          return this.getRoleName().includes('wali murid');
                      },
                      isDeveloper() {
                          return this.getRoleName().includes('developer');
                      }
                  }"
            >
                @csrf
                @method('PUT')
                
                {{-- Basic Information --}}
                <div class="p-4 bg-gray-50 rounded-xl space-y-4">
                    <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                        <x-ui.icon name="user" size="18" class="text-gray-400" />
                        Informasi Dasar
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Nama Pengenal (auto-generated, readonly display) --}}
                        <div class="form-group">
                            <label class="form-label">Nama Pengenal (Otomatis)</label>
                            <div class="form-input bg-slate-50 text-slate-600 cursor-not-allowed">{{ $user->nama }}</div>
                            <p class="form-help">Dibuat otomatis berdasarkan role & penempatan.</p>
                        </div>
                        
                        <x-forms.input 
                            name="username" 
                            label="Nama Lengkap" 
                            :value="$user->username"
                            required 
                            help="Nama asli pengguna (bisa dengan gelar). Untuk login & ditampilkan."
                        />
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <x-forms.input 
                            type="email" 
                            name="email" 
                            label="Email" 
                            :value="$user->email"
                            help="Opsional, untuk reset password & login Google."
                        />
                        
                        <x-forms.input 
                            name="phone" 
                            label="No. Telepon" 
                            :value="$user->phone"
                            placeholder="08xxxxxxxxxx" 
                        />
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group" x-data="{ show: false }">
                            <label for="password" class="form-label">Password Baru</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" id="password" name="password"
                                       class="form-input !pr-10 @error('password') error @enderror" placeholder="Kosongkan jika tidak diubah">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400">
                                    <div x-show="!show"><x-ui.icon name="eye" size="18" /></div>
                                    <div x-show="show"><x-ui.icon name="eye-off" size="18" /></div>
                                </button>
                            </div>
                            <p class="form-help">Biarkan kosong jika tidak ingin mengubah password.</p>
                            @error('password')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="form-group" x-data="{ show: false }">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation"
                                       class="form-input !pr-10">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400">
                                    <div x-show="!show"><x-ui.icon name="eye" size="18" /></div>
                                    <div x-show="show"><x-ui.icon name="eye-off" size="18" /></div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Role Selection --}}
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <x-forms.select 
                        name="role_id" 
                        label="Role Pengguna" 
                        required 
                        x-model="roleId"
                        :options="$roles"
                        optionValue="id"
                        optionLabel="nama_role"
                        :selected="$user->role_id"
                        placeholder="Pilih Role"
                    />
                </div>
                
                {{-- NIP, NI PPPK & NUPTK (untuk Guru, Waka, Kepala Sekolah, etc) --}}
                <div x-show="needsNipNuptk() || isDeveloper()" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="p-4 bg-amber-50 rounded-xl border border-amber-100 space-y-4">
                    <h4 class="font-semibold text-amber-800 flex items-center gap-2">
                        <x-ui.icon name="credit-card" size="18" class="text-amber-600" />
                        Data Kepegawaian
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <x-forms.input 
                            name="nip" 
                            label="NIP" 
                            :value="$user->nip"
                            placeholder="18 digit NIP"
                            help="Untuk PNS"
                        />
                        
                        <x-forms.input 
                            name="ni_pppk" 
                            label="NI PPPK" 
                            :value="$user->ni_pppk"
                            placeholder="Nomor Induk PPPK"
                            help="Untuk PPPK"
                        />
                        
                        <x-forms.input 
                            name="nuptk" 
                            label="NUPTK" 
                            :value="$user->nuptk"
                            placeholder="16 digit NUPTK"
                            help="Untuk Non-ASN"
                        />
                    </div>
                </div>
                
                {{-- Kelas (untuk Wali Kelas) --}}
                <div x-show="isWaliKelas() || isDeveloper()" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                    <div class="form-group mb-0">
                        <label for="kelas_id" class="form-label">
                            Kelas yang Diampu
                            <span x-show="isWaliKelas()" class="text-red-500">*</span>
                        </label>
                        <select id="kelas_id" name="kelas_id" class="form-input form-select @error('kelas_id') error @enderror">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas ?? [] as $k)
                                @php
                                    $isSelected = old('kelas_id', $user->kelasDiampu?->id) == $k->id;
                                @endphp
                                <option value="{{ $k->id }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }} {{ isset($k->jurusan) ? '(' . $k->jurusan->nama_jurusan . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="form-help">Pilih kelas yang akan menjadi tanggung jawab wali kelas ini.</p>
                        @error('kelas_id')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                {{-- Jurusan (untuk Kaprodi) --}}
                <div x-show="isKaprodi() || isDeveloper()" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                    <div class="form-group mb-0">
                        <label for="jurusan_id" class="form-label">
                            Jurusan yang Diampu
                            <span x-show="isKaprodi()" class="text-red-500">*</span>
                        </label>
                        <x-forms.select
                            name="jurusan_id" 
                            :options="$jurusan"
                            optionValue="id"
                            optionLabel="nama_jurusan"
                            :selected="$user->jurusanDiampu?->id"
                            placeholder="-- Pilih Jurusan --"
                        />
                        <p class="form-help">Pilih jurusan yang akan menjadi tanggung jawab Kaprodi ini.</p>
                    </div>
                </div>
                
                {{-- Siswa/Anak (untuk Wali Murid) --}}
                <div x-show="isWaliMurid() || isDeveloper()" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="p-4 bg-rose-50 rounded-xl border border-rose-100">
                    <div class="form-group mb-0">
                        <label for="siswa_ids" class="form-label">
                            Siswa/Anak yang Diasuh
                            <span x-show="isWaliMurid()" class="text-red-500">*</span>
                        </label>
                        <p class="text-sm text-gray-500 mb-3">Pilih siswa yang menjadi anak dari wali murid ini.</p>
                        <div class="max-h-48 overflow-y-auto border border-rose-200 rounded-lg p-3 bg-white space-y-2">
                            @php
                                $currentSiswaIds = $user->anakWali->pluck('id')->toArray();
                            @endphp
                            @forelse($siswa ?? [] as $s)
                                <label class="flex items-center gap-3 p-2 hover:bg-rose-50 rounded-lg cursor-pointer">
                                    <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" 
                                           {{ in_array($s->id, old('siswa_ids', $currentSiswaIds)) ? 'checked' : '' }}
                                           class="w-4 h-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-800">{{ $s->nama_siswa }}</p>
                                        <p class="text-sm text-gray-500">{{ $s->nisn }} • {{ $s->kelas->nama_kelas ?? '-' }}</p>
                                    </div>
                                </label>
                            @empty
                                <p class="text-gray-400 text-sm text-center py-4">Tidak ada data siswa</p>
                            @endforelse
                        </div>
                        @error('siswa_ids')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                {{-- Status --}}
                <div class="flex items-center">
                    <label class="flex items-center gap-3 cursor-pointer p-3 bg-gray-50 rounded-lg">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Akun Aktif</span>
                    </label>
                </div>
                
                {{-- User Info --}}
                <div class="p-4 bg-gray-50 rounded-xl">
                    <h4 class="font-semibold text-gray-700 mb-3">Info Akun</h4>
                    <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Dibuat</dt>
                            <dd class="font-medium">{{ $user->created_at?->format('d M Y H:i') ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Login Terakhir</dt>
                            <dd class="font-medium">{{ $user->last_login_at?->format('d M Y H:i') ?? 'Belum pernah' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Password Diubah</dt>
                            <dd class="font-medium">{{ $user->password_changed_at?->format('d M Y') ?? 'Belum' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Profil Lengkap</dt>
                            <dd class="font-medium">{{ $user->profile_completed_at?->format('d M Y') ?? 'Belum' }}</dd>
                        </div>
                    </dl>
                </div>
                
                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="btn btn-primary">
                        <x-ui.icon name="save" size="18" />
                        <span>Simpan Perubahan</span>
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
