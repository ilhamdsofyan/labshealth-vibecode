<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="{{ $appUiSettings['logo_url'] ?? asset('assets/img/Logo Labschool Bintaro.png') }}" alt="{{ $appUiSettings['school_name'] ?? 'LabsHealth UKS' }}" class="brand-logo-long">
        <img src="{{ $appUiSettings['logo_square_url'] ?? asset('assets/img/Logo.png') }}" alt="{{ $appUiSettings['school_name'] ?? 'LabsHealth UKS' }}" class="brand-logo-square">
    </div>

    <nav class="sidebar-nav">
        @isset($sidebarMenus)
            @foreach($sidebarMenus as $menu)
                @include('layouts._sidebar_item', ['menu' => $menu])
            @endforeach
        @endisset
    </nav>

    <div class="sidebar-footer">
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('settings.index'))
            <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
        @endif
        <form action="{{ route('logout') }}" method="POST" class="mt-1">
            @csrf
            <button type="submit" class="nav-link logout-link border-0 bg-transparent w-100 text-start">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
