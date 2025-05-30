@component('mail::message')
# {{ __('email.disposition_assigned.heading') }}

{{ __('email.disposition_assigned.greeting', ['name' => $user->name]) }}

{{ __('email.disposition_assigned.message', ['from' => $disposition->assigner?->name, 'subject' => $disposition->incoming_letter?->subject])  }}

{{ __('email.disposition_assigned.urgency_level', ['urgency' => __('email.disposition_assigned.urgency.' . $disposition->urgency)]) }}

> {{ $disposition->description }}

@component('mail::button', ['url' => route('incoming-letter.show', $disposition->incoming_letter) . '#' . $disposition->id])
    {{ __('email.disposition_assigned.button') }}
@endcomponent

{{ __('email.disposition_assigned.footer') }}

{{ __('email.disposition_assigned.thanks') }}

{{ config('app.name') }}
@endcomponent
