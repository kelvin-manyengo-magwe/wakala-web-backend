<div>
    @php
        $tillData = $getRecord()->till_no ?? [];
        // For logo display in table - define MNO logo paths again or pass them in
        $mnoLogosInTable = [
            'airtel'  => asset('images/mno/airtel-money-logo.png'),
            'halotel' => asset('images/mno/halo-pesa-logo.png'),
            'tigo'    => asset('images/mno/mixx-by-yas-logo.png'),
            'mpesa'   => asset('images/mno/mpesa-logo.jpg'),
        ];
    @endphp

    @if (!empty($tillData) && is_array($tillData))
        <div class="space-y-1">
            @foreach ($tillData as $till)
                @php
                    $mnoKey = $till['mno_key'] ?? 'unknown';
                    $mnoName = $mnoKey === 'unknown' ? 'N/A' : ($mnoDefinitions[$mnoKey][0] ?? ucfirst($mnoKey)); // Get Swahili name if using $mnoDefinitions from resource
                    $tillNumber = $till['till_no'] ?? 'N/A';
                    $logoUrl = $mnoLogosInTable[$mnoKey] ?? null;
                @endphp
                <div class="flex items-center space-x-1 text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 whitespace-nowrap">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $mnoName }}" class="h-4 w-4 object-contain">
                    @endif
                    <span>{{ $mnoName }}: {{ $tillNumber }}</span>
                </div>
            @endforeach
        </div>
    @else
        <span class="text-xs text-gray-500 dark:text-gray-400">-- Hamna --</span> {{-- "None" --}}
    @endif
</div>
