<div class="space-y-4">
    {{-- Event Information --}}
    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Event Information</h3>
        <dl class="grid grid-cols-2 gap-2 text-sm">
            <dt class="font-medium text-gray-600 dark:text-gray-400">Event:</dt>
            <dd class="text-gray-900 dark:text-gray-100">
                <span class="px-2 py-1 rounded text-xs font-semibold
                    @if($record->event === 'created') bg-green-100 text-green-800
                    @elseif($record->event === 'updated') bg-blue-100 text-blue-800
                    @elseif($record->event === 'deleted') bg-red-100 text-red-800
                    @elseif($record->event === 'login') bg-purple-100 text-purple-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ ucfirst(str_replace('_', ' ', $record->event)) }}
                </span>
            </dd>

            <dt class="font-medium text-gray-600 dark:text-gray-400">User:</dt>
            <dd class="text-gray-900 dark:text-gray-100">{{ $record->user?->name ?? 'System' }}</dd>

            <dt class="font-medium text-gray-600 dark:text-gray-400">Timestamp:</dt>
            <dd class="text-gray-900 dark:text-gray-100">{{ $record->created_at->format('d M Y, H:i:s') }}</dd>

            <dt class="font-medium text-gray-600 dark:text-gray-400">IP Address:</dt>
            <dd class="text-gray-900 dark:text-gray-100 font-mono text-xs">{{ $record->ip_address ?? 'N/A' }}</dd>

            @if($record->auditable_type)
            <dt class="font-medium text-gray-600 dark:text-gray-400">Model:</dt>
            <dd class="text-gray-900 dark:text-gray-100">{{ class_basename($record->auditable_type) }} #{{ $record->auditable_id }}</dd>
            @endif
        </dl>
    </div>

    {{-- Old Values (for updated/deleted events) --}}
    @php
        $oldValues = is_array($record->old_values) 
            ? $record->old_values 
            : json_decode($record->old_values ?? '{}', true);
    @endphp

    @if(!empty($oldValues) && in_array($record->event, ['updated', 'deleted']))
    <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
        <h3 class="text-sm font-semibold text-red-700 dark:text-red-300 mb-2">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            </svg>
            Old Values (Before)
        </h3>
        <div class="bg-white dark:bg-gray-800 p-3 rounded border border-red-200 dark:border-red-800">
            <pre class="text-xs text-gray-800 dark:text-gray-200 overflow-x-auto">{{ json_encode($oldValues, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
    @endif

    {{-- New Values (for created/updated events) --}}
    @php
        $newValues = is_array($record->new_values) 
            ? $record->new_values 
            : json_decode($record->new_values ?? '{}', true);
    @endphp

    @if(!empty($newValues) && in_array($record->event, ['created', 'updated']))
    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
        <h3 class="text-sm font-semibold text-green-700 dark:text-green-300 mb-2">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Values (After)
        </h3>
        <div class="bg-white dark:bg-gray-800 p-3 rounded border border-green-200 dark:border-green-800">
            <pre class="text-xs text-gray-800 dark:text-gray-200 overflow-x-auto">{{ json_encode($newValues, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
    @endif

    {{-- Changed Fields Summary (for updates) --}}
    @if($record->event === 'updated' && !empty($oldValues) && !empty($newValues))
    @php
        $changed = array_keys(array_diff_assoc($newValues, $oldValues));
    @endphp
    
    @if(!empty($changed))
    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
        <h3 class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-2">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            Changed Fields ({{ count($changed) }})
        </h3>
        <div class="space-y-2">
            @foreach($changed as $field)
            <div class="bg-white dark:bg-gray-800 p-3 rounded border border-blue-200 dark:border-blue-800">
                <div class="font-medium text-sm text-gray-700 dark:text-gray-300 mb-1">{{ $field }}</div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-red-600 dark:text-red-400 font-semibold">From:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $oldValues[$field] ?? 'null' }}</span>
                    </div>
                    <div>
                        <span class="text-green-600 dark:text-green-400 font-semibold">To:</span>
                        <span class="text-gray-600 dark:text-gray-400">{{ $newValues[$field] ?? 'null' }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif

    {{-- Failed Login Info --}}
    @if($record->event === 'failed_login' && !empty($newValues))
    <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
        <h3 class="text-sm font-semibold text-yellow-700 dark:text-yellow-300 mb-2">
            ⚠️ Failed Login Attempt
        </h3>
        <dl class="space-y-1 text-sm">
            @if(isset($newValues['email']))
            <div class="flex">
                <dt class="font-medium text-gray-600 dark:text-gray-400 w-24">Email:</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $newValues['email'] }}</dd>
            </div>
            @endif
            @if(isset($newValues['reason']))
            <div class="flex">
                <dt class="font-medium text-gray-600 dark:text-gray-400 w-24">Reason:</dt>
                <dd class="text-gray-900 dark:text-gray-100">{{ $newValues['reason'] }}</dd>
            </div>
            @endif
        </dl>
    </div>
    @endif

    {{-- No Data --}}
    @if(empty($oldValues) && empty($newValues))
    <div class="bg-gray-50 dark:bg-gray-800 p-8 rounded-lg text-center">
        <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
        <p class="text-sm text-gray-600 dark:text-gray-400">No detailed change data available for this event.</p>
    </div>
    @endif
</div>