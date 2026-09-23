<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<body style="margin:0; padding:0; background:#f3f6fa; font-family:Arial,Helvetica,sans-serif; color:#1f2937;">
    @php
        $brandName = $settings->project_name ?: config('app.name');
        $brandShortName = $settings->short_name ?: 'GRMS';
        $status = str($data['to_status'])->replace('_', ' ')->title();
        $logoCid = $logoPath ? $message->embed($logoPath) : null;
    @endphp

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f6fa; padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px; background:#ffffff; border:1px solid #dbe3ec; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td style="background:#14213d; padding:24px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle;">
                                        @if($logoCid)
                                            <img src="{{ $logoCid }}" width="54" height="54" alt="{{ $brandShortName }}" style="display:block; width:54px; height:54px; object-fit:contain; background:#ffffff; border-radius:8px; padding:5px;">
                                        @endif
                                    </td>
                                    <td style="vertical-align:middle; padding-left:14px;">
                                        <div style="font-size:19px; line-height:1.25; font-weight:700; color:#ffffff;">{{ $brandName }}</div>
                                        <div style="margin-top:4px; font-size:12px; color:#cbd5e1;">Grievance Redress Management System</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:34px 28px 28px;">
                            <div style="font-size:22px; line-height:1.3; font-weight:700; color:#14213d;">Grievance status update</div>
                            <p style="margin:14px 0 0; font-size:15px; line-height:1.7;">Hello{{ !empty($data['contact_name']) ? ' '.$data['contact_name'] : '' }},</p>
                            <p style="margin:10px 0 24px; font-size:15px; line-height:1.7; color:#4b5563;">
                                Your grievance has been recorded or updated in the Grievance Redress Management System.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #dbe3ec; border-radius:8px; overflow:hidden;">
                                <tr>
                                    <td style="padding:14px 16px; background:#f8fafc; border-bottom:1px solid #dbe3ec; font-size:13px; color:#64748b;">Reference number</td>
                                    <td style="padding:14px 16px; background:#f8fafc; border-bottom:1px solid #dbe3ec; text-align:right; font-size:14px; font-weight:700; color:#14213d;">{{ $data['reference_no'] }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px; border-bottom:1px solid #dbe3ec; font-size:13px; color:#64748b;">Current status</td>
                                    <td style="padding:14px 16px; border-bottom:1px solid #dbe3ec; text-align:right; font-size:14px; font-weight:700; color:#1d4ed8;">{{ $status }}</td>
                                </tr>
                                @if($data['from_status'] && $data['from_status'] !== $data['to_status'])
                                    <tr>
                                        <td style="padding:14px 16px; border-bottom:1px solid #dbe3ec; font-size:13px; color:#64748b;">Previous status</td>
                                        <td style="padding:14px 16px; border-bottom:1px solid #dbe3ec; text-align:right; font-size:14px; color:#334155;">{{ str($data['from_status'])->replace('_', ' ')->title() }}</td>
                                    </tr>
                                @endif
                                @if($data['reason'])
                                    <tr>
                                        <td style="padding:14px 16px; font-size:13px; color:#64748b;">Note</td>
                                        <td style="padding:14px 16px; text-align:right; font-size:14px; color:#334155;">{{ $data['reason'] }}</td>
                                    </tr>
                                @endif
                            </table>

                            <p style="margin:24px 0 0; font-size:14px; line-height:1.7; color:#4b5563;">
                                Keep this reference number safe for tracking and future communication.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 28px; background:#f8fafc; border-top:1px solid #e5e7eb;">
                            <div style="font-size:13px; line-height:1.6; font-weight:700; color:#14213d;">{{ $brandName }}</div>
                            @if($settings->footer_text)
                                <div style="margin-top:5px; font-size:12px; line-height:1.6; color:#64748b;">{{ $settings->footer_text }}</div>
                            @endif
                            @if($settings->email || $settings->phone || $settings->address_line)
                                <div style="margin-top:8px; font-size:12px; line-height:1.6; color:#64748b;">
                                    {{ collect([$settings->email, $settings->phone, $settings->address_line])->filter()->implode(' · ') }}
                                </div>
                            @endif
                            <div style="margin-top:12px; font-size:11px; color:#94a3b8;">This is an automated message. Please do not reply directly to this email.</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
