{{-- Dark-mode topo background; hidden in light mode (aurora background takes over there
     instead, see customer-aurora-bg.blade.php). --}}
<div class="hidden lg:block fixed inset-0 z-0 cust-page-bg" style="background-image: url('{{ asset('images/crdboard.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
<div class="lg:hidden cust-page-bg" style="position: fixed; inset: 0; overflow: hidden; z-index: 0;">
    <div style="width: 100vh; height: 100vw; transform: rotate(-90deg); transform-origin: top left; position: absolute; top: 100%; left: 0; background-image: url('{{ asset('images/crdboard.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
</div>
