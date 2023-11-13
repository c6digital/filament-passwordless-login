<x-mail::message>
## Your Login Link

Click the button below to login to {{ config('app.name') }}.

This button will expire in 30 minutes.

<x-mail::button :url="$url">
Login
</x-mail::button>

<small>If you didn't request this email, you can safely ignore it.</small>
</x-mail::message>
