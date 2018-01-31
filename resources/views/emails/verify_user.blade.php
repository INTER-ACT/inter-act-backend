@component('mail::message')
# E-Mail bestätigen

Bitte bestätigen Sie Ihre E-Mail-Adresse, indem Sie auf den folgenden Link klicken:

@component('mail::button', ['url' => $verificationUrl, 'color' => 'red'])
    E-Mail bestätigen
@endcomponent

Vielen Dank und viel Spaß beim Diskutieren,<br>
Ihr Inter-Act Team
@endcomponent