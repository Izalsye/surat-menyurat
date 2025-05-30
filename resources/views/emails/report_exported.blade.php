@component('mail::message')
{{ __('email.report_exported.greeting', ['name' => $user->name]) }}

{{ __('email.report_exported.message')  }}

{{ __('email.report_exported.thanks') }}

{{ config('app.name') }}
@endcomponent
