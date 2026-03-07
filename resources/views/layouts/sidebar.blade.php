<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="bi bi-heart-pulse"></i>
        </div>
        <h5>LabsHealth</h5>
    </div>

    <nav class="sidebar-nav">
        @isset($sidebarMenus)
            @foreach($sidebarMenus as $menu)
                @include('layouts._sidebar_item', ['menu' => $menu])
            @endforeach
        @endisset
    </nav>
</aside>
