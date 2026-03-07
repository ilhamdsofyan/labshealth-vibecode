@php
    $hasChildren = $menu->activeChildren && $menu->activeChildren->isNotEmpty();
    $isActive = $menu->route_name ? request()->routeIs($menu->route_name . '*') : false;
    
    // Check if any child is active
    if (!$isActive && $hasChildren) {
        $isActive = $menu->activeChildren->contains(function($child) {
            return $child->route_name ? request()->routeIs($child->route_name . '*') : false;
        });
    }
@endphp

<div class="nav-item">
    @if($hasChildren)
        <a class="nav-link {{ $isActive ? 'active' : '' }} dropdown-toggle" 
           href="#menu-{{ $menu->id }}" 
           data-bs-toggle="collapse" 
           role="button" 
           aria-expanded="{{ $isActive ? 'true' : 'false' }}">
            <i class="bi {{ $menu->icon ?? 'bi-circle' }}"></i>
            <span>{{ $menu->name }}</span>
            <i class="bi bi-chevron-down ms-auto small toggle-icon"></i>
        </a>
        <ul class="submenu collapse {{ $isActive ? 'show' : '' }}" id="menu-{{ $menu->id }}">
            @foreach($menu->activeChildren as $child)
                <li>
                    @include('layouts._sidebar_item', ['menu' => $child])
                </li>
            @endforeach
        </ul>
    @else
        <a href="{{ $menu->route_name ? route($menu->route_name) : '#' }}"
           class="nav-link {{ $isActive ? 'active' : '' }}">
            <i class="bi {{ $menu->icon ?? 'bi-circle' }}"></i>
            <span>{{ $menu->name }}</span>
        </a>
    @endif
</div>
