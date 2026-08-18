@props([
    'lead',
    'followUp' => null,
    'followUpTypes' => [],
    'outcomes' => [],
    'compact' => false,
])

<x-crm.interaction-result-form
    :lead="$lead"
    :follow-up="$followUp"
    :follow-up-types="$followUpTypes"
    :compact="$compact"
/>
