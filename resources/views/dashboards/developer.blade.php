@extends('layouts.app')

@section('page-header', false)

@section('content')
<div class="space-y-6">
    {{-- Warning Banner --}}
    @if($isProduction)
        <div class="alert alert-danger">
            <x-ui.icon name="alert-triangle" class="alert-icon" />
            <div class="alert-content">
                <p class="alert-title">⚠️ Production Environment</p>
                <p class="alert-message">You are accessing the developer console in a production environment. Be careful!</p>
            </div>
        </div>
    @endif
    
    {{-- System Info --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="stat-card">
            <div class="stat-card-icon primary">
                <x-ui.icon name="terminal" size="24" />
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Laravel Version</p>
                <p class="stat-card-value text-xl">{{ app()->version() }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-card-icon warning">
                <x-ui.icon name="settings" size="24" />
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">PHP Version</p>
                <p class="stat-card-value text-xl">{{ phpversion() }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-card-icon {{ $isProduction ? 'danger' : 'success' }}">
                <x-ui.icon name="lock" size="24" />
            </div>
            <div class="stat-card-content">
                <p class="stat-card-label">Environment</p>
                <p class="stat-card-value text-xl">{{ app()->environment() }}</p>
            </div>
        </div>
    </div>
    
    {{-- Role Switcher --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title flex items-center gap-2">
                <x-ui.icon name="users" size="18" class="text-gray-400" />
                Role Switcher
            </h3>
        </div>
        <div class="card-body">
            <p class="text-gray-600 mb-4">Switch between roles to test different dashboard views.</p>
            
            @php
                $roles = \App\Models\Role::all();
                $currentOverride = session('developer_role_override');
            @endphp
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach($roles as $role)
                    <form action="{{ route('developer.switch-role') }}" method="POST">
                        @csrf
                        <input type="hidden" name="role_id" value="{{ $role->id }}">
                        <button type="submit" class="w-full btn {{ $currentOverride == $role->id ? 'btn-primary' : 'btn-secondary' }}">
                            {{ $role->nama_role }}
                        </button>
                    </form>
                @endforeach
            </div>
            
            @if($currentOverride)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <form action="{{ route('developer.reset-role') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline">
                            <x-ui.icon name="refresh-cw" size="18" />
                            <span>Reset to Developer</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
    
    {{-- Quick Links --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Quick Tools</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('audit.activity.index') }}" class="card flex items-center gap-4 p-4 hover:border-slate-200 transition-all">
                    <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                        <x-ui.icon name="activity" size="20" />
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Audit Log</h4>
                        <p class="text-sm text-gray-500">View system activity</p>
                    </div>
                </a>
                
                <a href="{{ route('users.index') }}" class="card flex items-center gap-4 p-4 hover:border-emerald-200 transition-all">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <x-ui.icon name="user" size="20" />
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Manage Users</h4>
                        <p class="text-sm text-gray-500">User administration</p>
                    </div>
                </a>
                
                <a href="{{ route('frequency-rules.index') }}" class="card flex items-center gap-4 p-4 hover:border-violet-200 transition-all">
                    <div class="w-10 h-10 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center">
                        <x-ui.icon name="settings" size="20" />
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">Rules Engine</h4>
                        <p class="text-sm text-gray-500">Configure rules</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
