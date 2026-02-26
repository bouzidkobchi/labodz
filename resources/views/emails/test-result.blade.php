<!DOCTYPE html>
<html dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.medical_results') }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.6;
            color: #2d3748;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f7fafc;
        }
        .container {
            background: white;
            padding: 0;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #3182ce, #2c5282);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 { margin: 0; font-size: 28px; letter-spacing: 1px; }
        .header p { margin: 5px 0 0; opacity: 0.9; font-size: 14px; }
        
        .content { padding: 40px 30px; }
        
        .welcome-msg { font-size: 18px; font-weight: bold; margin-bottom: 20px; color: #2d3748; }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        .info-item { font-size: 13px; color: #718096; }
        .info-value { font-weight: bold; color: #2d3748; font-size: 15px; margin-top: 2px; }

        .cta-box {
            text-align: center;
            padding: 30px;
            background: #ebf8ff;
            border-radius: 12px;
            margin: 30px 0;
            border: 1px solid #bee3f8;
        }
        .btn-portal {
            display: inline-block;
            background: #3182ce;
            color: white !important;
            padding: 14px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 15px;
            box-shadow: 0 4px 6px rgba(49, 130, 206, 0.2);
        }
        
        .notes {
            border-left: 4px solid #ed8936;
            padding: 15px 20px;
            background: #fffaf0;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        [dir="rtl"] .notes { border-left: none; border-right: 4px solid #ed8936; border-radius: 8px 0 0 8px; }

        .footer {
            padding: 30px;
            text-align: center;
            font-size: 12px;
            color: #a0aec0;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>labo.dz</h1>
            <p>{{ __('messages.expertise_diagnostics') ?? 'Expertise en Diagnostics' }}</p>
        </div>
        
        <div class="content">
            <div class="welcome-msg">
                {{ __('messages.hello') ?? 'Bonjour' }}, {{ $patient->name }}
            </div>
            
            <p>{{ __('messages.results_ready_desc') }}</p>
            
            <div class="info-grid">
                <div>
                    <div class="info-item">{{ __('messages.case_number') }}</div>
                    <div class="info-value">V-{{ $reservation->id }}</div>
                </div>
                <div>
                    <div class="info-item">{{ __('messages.date') }}</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($reservation->analysis_date)->format('d/m/Y') }}</div>
                </div>
            </div>

            <div class="cta-box">
                <p style="margin-bottom: 10px; font-weight: 500;">{{ __('messages.view_results_online_desc') ?? 'Vous pouvez consulter et télécharger vos résultats complets via notre portail sécurisé.' }}</p>
                <a href="{{ route('access') }}" class="btn-portal">
                    {{ __('messages.access_patient_portal') ?? 'Accéder au Portail' }}
                </a>
                <p style="margin-top: 15px; font-size: 12px; color: #718096;">
                    {{ __('messages.login_instruction_reminder') ?? 'Utilisez votre N° de Dossier et votre numéro de téléphone pour vous connecter.' }}
                </p>
            </div>
            
            @if($additional_notes)
            <div class="notes">
                <strong style="display: block; margin-bottom: 5px; color: #c05621;">{{ __('messages.additional_notes') }}:</strong>
                <p style="margin: 0; font-size: 14px; color: #744210;">{{ $additional_notes }}</p>
            </div>
            @endif
            
            <div style="font-size: 14px; color: #718096;">
                <p>• {{ __('messages.consult_doctor_tip') ?? 'Veuillez consulter votre médecin pour l\'interprétation de ces résultats.' }}</p>
            </div>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} labo.dz - {{ __('messages.all_rights_reserved') ?? 'Tous droits réservés' }}</p>
            <p>{{ __('messages.auto_generated_email') ?? 'Cet e-mail a été généré automatiquement, merci de ne pas y répondre.' }}</p>
        </div>
    </div>
</body>
</html>
