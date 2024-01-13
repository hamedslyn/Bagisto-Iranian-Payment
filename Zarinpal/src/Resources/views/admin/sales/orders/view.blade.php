@if($order->payment->method == 'zarinpal')
    @if ($order->canRefund())
        @if ($order->canShip())
            @push('scripts')
                <script>
                    $( ".page-action a:nth-last-child(2)" ).remove();
                </script>
            @endpush
        @else
            @push('scripts')
                <script>
                    $( ".page-action a:nth-last-child(1)" ).remove();
                </script>
            @endpush
        @endif
    @endif
@endif
