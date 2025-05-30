@component('mail::message')
# {{ __('email.account_created.welcome_heading', ['app' => config('app.name')]) }}

{{ __('email.account_created.hello', ['name' => $user->name]) }},

{{ __('email.account_created.account_created', ['app' => config('app.name')])  }}

{{ __('email.account_created.credentials') }}

- **{{ __('email.account_created.email') }}:** {{ $user->email }}
- **{{ __('email.account_created.password') }}:** {{ $password }}

@component('mail::button', ['url' => route('login')])
    {{ __('email.account_created.login_button', ['app' => config('app.name')]) }}
@endcomponent

> {{ __('email.account_created.security_notice') }}

{{ __('email.account_created.contact_admin') }}

{{ __('email.account_created.thanks') }}

{{ config('app.name') }}
@endcomponent
