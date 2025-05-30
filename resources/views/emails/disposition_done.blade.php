@component('mail::message')
# {{ __('email.disposition_done.heading') }}

{{ __('email.disposition_done.greeting', ['name' => $user->name]) }}

{{ __('email.disposition_done.message', ['from' => $disposition->assigner?->name, 'subject' => $disposition->incoming_letter?->subject, 'by' => $disposition->assignee?->name ?? __('email.disposition_done.system')])  }}

> {{ $disposition->description }}

@component('mail::button', ['url' => route('incoming-letter.show', $disposition->incoming_letter) . '#' . $disposition->id])
    {{ __('email.disposition_done.button') }}
@endcomponent

{{ __('email.disposition_done.footer') }}

{{ __('email.disposition_done.thanks') }}

{{ config('app.name') }}
@endcomponent
