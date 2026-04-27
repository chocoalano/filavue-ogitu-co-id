# Project Memory

## Filament v5 Schema View Components — Record Access

In `View::make('...')` schema components (infolist/form), the Eloquent record is
injected as **`$record`** (a direct PHP variable), NOT via `$getRecord()`.

```blade
{{-- CORRECT --}}
@php $pageRecord = $record; @endphp

{{-- WRONG — $getRecord may not be a defined callable --}}
@php $record = $getRecord(); @endphp
```

## Page Blocks Storage Format

Page 13's `blocks` field is double-encoded JSON AND UUID-keyed (Filament Builder raw state).
Required normalization in Blade views:
1. If `$record->blocks` returns a string → `json_decode` again
2. If the decoded array is NOT a list (UUID keys) → `array_values()`
3. Filament Repeater `items` also uses UUID keys → apply same `array_values()`
