<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.06);">
                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #6366f1, #a855f7, #ec4899); padding:40px 32px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:24px; font-weight:700;">SSJTimer</h1>
                            <p style="margin:8px 0 0; color:rgba(255,255,255,0.85); font-size:14px;">Euer gemeinsamer Countdown</p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:40px 32px;">
                            <h2 style="margin:0 0 16px; color:#1e293b; font-size:20px; font-weight:700;">
                                Hallo!
                            </h2>
                            <p style="margin:0 0 24px; color:#475569; font-size:16px; line-height:1.6;">
                                <strong style="color:#1e293b;">{{ $inviter->name }}</strong> möchte den SSJTimer mit dir teilen.
                                Ihr könnt dann gemeinsam Flüge, Besuche und Dates planen und einen Live-Countdown bis zum nächsten Wiedersehen sehen.
                            </p>

                            {{-- CTA Button --}}
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:8px 0 32px;">
                                        <a href="{{ url('/join/' . $invitation->token) }}"
                                           style="display:inline-block; padding:16px 40px; background:linear-gradient(135deg, #6366f1, #a855f7); color:#ffffff; text-decoration:none; font-size:16px; font-weight:600; border-radius:12px;">
                                            Einladung annehmen
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px; color:#94a3b8; font-size:13px;">
                                Dieser Link ist 48 Stunden gültig. Falls du noch keinen Account hast, wirst du zur Registrierung weitergeleitet.
                            </p>

                            <hr style="border:none; border-top:1px solid #e2e8f0; margin:24px 0;">

                            <p style="margin:0; color:#cbd5e1; font-size:12px; text-align:center;">
                                Du hast diese Mail erhalten, weil {{ $inviter->name }} ({{ $inviter->email }}) dich eingeladen hat.
                                Falls du diese Einladung nicht erwartet hast, kannst du diese E-Mail ignorieren.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
