<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New quote request</title>
</head>
<body style="margin:0; padding:0; background-color:#FAF7F2; font-family: 'Plus Jakarta Sans', Helvetica, Arial, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#FAF7F2; padding:32px 16px;">
<tr>
<td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background-color:#FFFFFF; border-radius:16px; overflow:hidden; border:1px solid rgba(11,59,46,0.08);">

<tr>
<td style="background-color:#0B3B2E; padding:28px 32px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td style="vertical-align:middle;">
<table role="presentation" cellpadding="0" cellspacing="0">
<tr>
<td style="width:36px; height:36px; background-color:#C89B3C; border-radius:999px; text-align:center; vertical-align:middle; font-size:16px;">&#10024;</td>
<td style="padding-left:10px; font-family: Georgia, 'Times New Roman', serif; font-size:20px; color:#FAF7F2; font-weight:600;">N&egrave;g Mawon</td>
</tr>
</table>
</td>
<td align="right" style="vertical-align:middle; font-family: 'Plus Jakarta Sans', Helvetica, Arial, sans-serif; font-size:11px; letter-spacing:0.08em; text-transform:uppercase; color:#C89B3C;">New Lead</td>
</tr>
</table>
</td>
</tr>

<tr>
<td style="padding:32px 32px 8px;">
<p style="margin:0 0 4px; font-family: 'Plus Jakarta Sans', Helvetica, Arial, sans-serif; font-size:11px; letter-spacing:0.08em; text-transform:uppercase; color:#C89B3C; font-weight:600;">NGM Cleaning Website</p>
<h1 style="margin:0; font-family: Georgia, 'Times New Roman', serif; font-size:26px; line-height:1.3; color:#0B3B2E;">New quote request from {{ $senderName }}</h1>
</td>
</tr>

<tr>
<td style="padding:20px 32px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#FAF7F2; border-radius:12px; border:1px solid rgba(11,59,46,0.08);">
<tr>
<td style="padding:20px 24px;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
<tr>
<td style="padding-bottom:12px; font-size:11px; letter-spacing:0.06em; text-transform:uppercase; color:#8A8176; width:90px; vertical-align:top;">Name</td>
<td style="padding-bottom:12px; font-size:15px; color:#1A1A1A; vertical-align:top;">{{ $senderName }}</td>
</tr>
<tr>
<td style="padding-bottom:12px; font-size:11px; letter-spacing:0.06em; text-transform:uppercase; color:#8A8176; width:90px; vertical-align:top;">Email</td>
<td style="padding-bottom:12px; font-size:15px; vertical-align:top;"><a href="mailto:{{ $senderEmail }}" style="color:#0B3B2E; text-decoration:none; font-weight:600;">{{ $senderEmail }}</a></td>
</tr>
<tr>
<td style="font-size:11px; letter-spacing:0.06em; text-transform:uppercase; color:#8A8176; width:90px; vertical-align:top;">Phone</td>
<td style="font-size:15px; vertical-align:top;"><a href="tel:{{ $senderPhone }}" style="color:#0B3B2E; text-decoration:none; font-weight:600;">{{ $senderPhone }}</a></td>
</tr>
</table>

</td>
</tr>
</table>
</td>
</tr>

<tr>
<td style="padding:20px 32px 0;">
<p style="margin:0 0 8px; font-size:11px; letter-spacing:0.06em; text-transform:uppercase; color:#8A8176;">Message</p>
<p style="margin:0; font-size:15px; line-height:1.6; color:#1A1A1A; white-space:pre-line;">{{ $messageBody }}</p>
</td>
</tr>

<tr>
<td style="padding:28px 32px 32px;">
<table role="presentation" cellpadding="0" cellspacing="0">
<tr>
<td style="background-color:#0B3B2E; border-radius:999px;">
<a href="mailto:{{ $senderEmail }}" style="display:inline-block; padding:12px 24px; font-size:14px; font-weight:600; color:#FAF7F2; text-decoration:none;">Reply to {{ $senderName }}</a>
</td>
</tr>
</table>
</td>
</tr>

<tr>
<td style="padding:20px 32px; border-top:1px solid rgba(11,59,46,0.08); background-color:#FAF7F2;">
<p style="margin:0; font-size:12px; color:#8A8176;">This lead came in through the quote form on ngmcleaning.com. Reply directly to this email to reach {{ $senderName }}.</p>
</td>
</tr>

</table>
</td>
</tr>
</table>
</body>
</html>
