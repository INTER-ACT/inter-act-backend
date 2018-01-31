@component('mail::message')
# Passwort bestätigen

Bitte bestätigen Sie die Erneuerung Ihres Passwortes durch das Klicken des folgenden Buttons:

@component('mail::button', ['url' => $verificationUrl])
    Passwort erneuern
@endcomponent

Vielen Dank und viel Spaß beim Diskutieren,<br>
Ihr Inter-Act Team
@endcomponent