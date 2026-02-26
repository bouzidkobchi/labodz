<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Confirmation de Rendez-vous - labo.dz</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            color: #2d3748;
            line-height: 1.4;
            background-color: #f7fafc;
        }

        .page-container {
            padding: 40px;
            background-color: white;
            min-height: 297mm;
        }

        .header {
            background-color: #2c5282;
            color: white;
            padding: 30px;
            text-align: center;
            border-bottom: 5px solid #ecc94b;
            margin: -40px -40px 30px -40px;
        }

        .header h1 {
            margin: 0;
            font-size: 26pt;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0 0;
            font-size: 11pt;
            opacity: 0.9;
        }

        .doc-badge {
            display: inline-block;
            background-color: #ecc94b;
            color: #2d3748;
            padding: 8px 25px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 14pt;
            margin-top: -20px;
            text-align: center;
            width: 80%;
            margin-left: 10%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        /* MEDICAL REPORT DESIGN OVERRIDES */
        .medical-report-header {
            background-color: white !important;
            color: #2c5282 !important;
            border-bottom: 2px solid #2c5282 !important;
            text-align: left !important;
            padding: 20px 0 !important;
            margin-bottom: 40px !important;
        }

        .medical-report-header h1 {
            color: #2c5282 !important;
        }

        .medical-report-header p {
            color: #4a5568 !important;
        }

        .medical-report-badge {
            background-color: #2f855a !important;
            color: white !important;
            border-radius: 5px !important;
            width: auto !important;
            margin-left: 0 !important;
            padding: 5px 20px !important;
            font-size: 12pt !important;
            margin-top: 0 !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }

        .eligibility-report-badge {
            background-color: #3182ce !important;
            color: white !important;
            border-radius: 5px !important;
            width: auto !important;
            margin-left: 0 !important;
            padding: 5px 20px !important;
            font-size: 12pt !important;
            margin-top: 0 !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            color: #2c5282;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
            text-transform: uppercase;
        }

        .info-card {
            background-color: #edf2f7;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
        }

        .info-table {
            width: 100%;
        }

        .info-table td {
            padding: 6px 0;
        }

        .label {
            font-weight: bold;
            color: #4a5568;
            width: 180px;
            font-size: 10pt;
        }

        .value {
            font-weight: 600;
            color: #2d3748;
            font-size: 11pt;
        }

        .analysis-table {
            width: 100%;
            border-collapse: collapse;
        }

        .analysis-table th {
            text-align: left;
            background-color: #2c5282;
            color: white;
            padding: 12px;
            font-size: 10pt;
        }

        .analysis-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        .total-row td {
            font-weight: bold;
            font-size: 14pt;
            color: #2c5282;
            padding-top: 20px;
            text-align: right;
        }

        /* EYE-CATCHING PREPARATION BOX */
        .prep-box {
            background-color: #fffaf0;
            border: 2px solid #ecc94b;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            position: relative;
        }

        .prep-header {
            color: #b7791f;
            font-weight: bold;
            font-size: 16pt;
            margin-bottom: 15px;
            text-align: center;
            text-transform: uppercase;
        }

        .prep-item {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 5px solid #ecc94b;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .prep-item strong {
            display: block;
            color: #2c5282;
            margin-bottom: 5px;
            font-size: 12pt;
        }

        .prep-text {
            color: #4a5568;
            font-size: 11pt;
            line-height: 1.6;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9pt;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }

        .footer p {
            margin: 2px 0;
        }

        .signature-section {
            margin-top: 80px;
            width: 100%;
        }

        .signature-box {
            float: right;
            width: 250px;
            text-align: center;
            border-top: 1px solid #cbd5e0;
            padding-top: 10px;
            font-size: 10pt;
            color: #4a5568;
        }
    </style>
</head>
</head>

<body>
    <div class="page-container">
        <div class="header {{ (($isResultReport ?? false) || ($isEligibilityReport ?? false)) ? 'medical-report-header' : '' }}">
            @if($barcode)
                <div style="float: right; text-align: center;">
                    <img src="{{ $barcode }}" width="120" alt="QR Code" style="display: block; margin-bottom: 5px;">
                    <span style="font-size: 10px; color: #666; font-weight: bold; text-transform: uppercase;">
                        {{ ($isResultReport ?? false) ? 'Rapport Médical QR' : (($isEligibilityReport ?? false) ? 'Évaluation QR' : 'Visite Médicale QR') }}
                    </span>
                </div>
            @endif
            <h1 style="text-align: left;">labo.dz</h1>
            <p style="text-align: left; font-weight: bold; color: #2c5282;">Laboratoire de Biologie Médicale</p>
            <p style="text-align: left;">Expertise en Analyses Médicales & Diagnostics</p>
            <p style="text-align: left;">Tel: 0550 12 34 56 | E-mail: info@labo-dz.com</p>
        </div>

        <div class="doc-badge {{ ($isResultReport ?? false) ? 'medical-report-badge' : (($isEligibilityReport ?? false) ? 'eligibility-report-badge' : '') }}">
            @if($isResultReport ?? false)
                RAPPORT DE RÉSULTATS MÉDICAUX
            @elseif($isEligibilityReport ?? false)
                RAPPORT D'ÉVALUATION D'ÉLIGIBILITÉ MÉDICALE
            @else
                CONFIRMATION DE RENDEZ-VOUS
            @endif
        </div>

        <div class="section" style="margin-top: 40px;">
            <div class="section-title">Informations du Patient</div>
            <div class="info-card">
                <table class="info-table">
                    <tr>
                        <td class="label">Nom Complet:</td>
                        <td class="value">{{ $patientName }}</td>
                    </tr>
                    <tr>
                        <td class="label">Date du RDV:</td>
                        <td class="value">{{ \Carbon\Carbon::parse($appointmentDate)->format('d/m/Y') }} @if($appointmentTime) à {{ $appointmentTime }} @endif</td>
                    </tr>
                    <tr>
                        <td class="label">Sexe:</td>
                        <td class="value">{{ ($reservation->patient->gender ?? $reservation->gender ?? 'male') == 'male' ? 'Masculin' : 'Féminin' }}</td>
                    </tr>
                    <tr>
                        <td class="label">N° de Dossier:</td>
                        <td class="value">#{{ ($type == 'request' ? 'R-' : 'V-') . $reservation->id }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if(!($isResultReport ?? false) && !($isEligibilityReport ?? false))
        <div class="section">
            <div class="section-title">Analyses Demandées</div>
            <table class="analysis-table">
                <thead>
                    <tr>
                        <th>Désignation de l'Analyse</th>
                        <th style="text-align: right;">Prix</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @foreach($analyses as $analysis)
                    <tr>
                        <td><strong>{{ $analysis->name_fr }}</strong></td>
                        <td style="text-align: right;">{{ number_format($analysis->price, 2) }} DA</td>
                    </tr>
                    @php $total += $analysis->price; @endphp
                    @endforeach
                    <tr class="total-row">
                        <td style="border: none;">MONTANT TOTAL:</td>
                        <td style="border: none;">{{ number_format($total, 2) }} DA</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        @if($isEligibilityReport ?? false)
        <div class="section">
            <div class="section-title">Sommaire de l'Évaluation</div>
            <table class="analysis-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Analyse</th>
                        <th style="width: 25%; text-align: center;">Statut Clinical</th>
                        <th>Notes d'Évaluation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eligibilityResults as $res)
                    <tr>
                        <td><strong>{{ $res['name'] }}</strong></td>
                        <td style="text-align: center;">
                            <span style="padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 9pt; 
                                {{ $res['status'] == 'block' ? 'background: #fed7d7; color: #9b2c2c;' : ($res['status'] == 'warning' ? 'background: #feebc8; color: #7b341e;' : 'background: #c6f6d5; color: #22543d;') }}">
                                {{ $res['status'] == 'block' ? 'REJETÉ' : ($res['status'] == 'warning' ? 'AVERTISSEMENT' : 'ÉLIGIBLE') }}
                            </span>
                        </td>
                        <td style="font-size: 9pt; color: #4a5568;">
                            @if(count($res['notes']) > 0)
                                <ul style="margin: 0; padding-left: 15px;">
                                    @foreach($res['notes'] as $note)
                                        <li>{{ $note }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span style="color: #2f855a;">✓ Conforme au protocole</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Réponses au Questionnaire Diagnostique</div>
            <table class="analysis-table">
                <thead>
                    <tr>
                        <th style="width: 70%;">Question / Paramètre</th>
                        <th style="text-align: center;">Réponse du Patient</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patientAnswers as $qId => $answers)
                        @php $question = $answers->first()->question; @endphp
                        <tr>
                            <td>{{ $question->question }}</td>
                            <td style="text-align: center; font-weight: bold;">
                                @foreach($answers as $ans)
                                    <span style="border: 1px solid #e2e8f0; padding: 2px 8px; border-radius: 4px; margin: 0 2px;">{{ $ans->option->text }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" style="text-align: center; color: #718096;">Aucun questionnaire enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif

        @if($isResultReport ?? false)
        <div class="section">
            <div class="section-title">Résultats des Analyses</div>
            @php $hasCritical = false; @endphp
            <table class="analysis-table">
                <thead>
                    <tr>
                        <th>Paramètre / Analyse</th>
                        <th style="text-align: center;">Résultat</th>
                        <th style="text-align: center;">Unité</th>
                        <th>Valeurs de Référence</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analyses as $analysis)
                    @if($analysis->pivot->result_value)
                    @php if(($analysis->pivot->clinical_status ?? '') == 'CRITICAL') $hasCritical = true; @endphp
                    <tr>
                        <td style="width: 40%;">
                            <strong>{{ $analysis->name_fr }}</strong>
                            @if(($analysis->pivot->clinical_status ?? '') == 'CRITICAL')
                                <br><span style="color: #c53030; font-size: 8pt; font-weight: bold;">[VALEUR CRITIQUE]</span>
                            @endif
                        </td>
                        <td style="text-align: center; font-weight: bold; {{ ($analysis->pivot->clinical_status ?? '') == 'CRITICAL' ? 'color: #c53030;' : 'color: #2c5282;' }} font-size: 13pt;">
                            {{ $analysis->pivot->result_value }}
                        </td>
                        <td style="text-align: center; color: #4a5568;">{{ $analysis->pivot->unit }}</td>
                        <td style="font-size: 9pt; color: #718096; width: 30%;">{!! nl2br(e($analysis->pivot->reference_range)) !!}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>

            @if($hasCritical)
            <div style="margin-top: 20px; border: 2px solid #c53030; background: #fff5f5; padding: 15px; border-radius: 8px;">
                <h4 style="color: #c53030; margin-top: 0; margin-bottom: 5px;">⚠️ ALERTE : VALEUR CRITIQUE DÉTECTÉE</h4>
                <p style="color: #742a2a; font-size: 10pt; margin: 0;">
                    Certains résultats présentent des valeurs critiques nécessitant une attention médicale immédiate. 
                    Veuillez contacter votre médecin traitant sans délai.
                </p>
            </div>
            @endif
        </div>
        @endif

        @if(($isResultReport ?? false) || ($isEligibilityReport ?? false))
        <div class="signature-section">
            <div class="signature-box">
                Validation & Cachet Médical<br><br><br><br>
                <strong>Laboratoire labo.dz</strong>
            </div>
            <div style="clear: both;"></div>
        </div>
        @endif

        @if(!($isResultReport ?? false))
            @php
            $hasPreparation = $analyses->filter(function($analysis) {
                return !empty($analysis->prep_fr);
            })->count() > 0;
            @endphp

            @if($hasPreparation)
            <div class="prep-box">
                <div class="prep-header">
                    IMPORTANT : CONSIGNES DE PRÉPARATION
                </div>
                <p style="text-align: center; color: #744210; margin-bottom: 20px; font-weight: 500;">
                    Veuillez respecter ces consignes pour la précision de vos résultats
                </p>

                @foreach($analyses as $analysis)
                @if($analysis->prep_fr)
                <div class="prep-item">
                    <strong style="color: #2c5282;">&bull; {{ $analysis->name_fr }}</strong>
                    <div class="prep-text">
                        {{ $analysis->prep_fr }}
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            @endif
        @endif

        @if(!($isResultReport ?? false) && !($isEligibilityReport ?? false))
        <div class="important-notes" style="margin-top: 40px; border-top: 1px dashed #cbd5e0; padding-top: 20px;">
            <p style="font-size: 10pt; color: #4a5568;">
                &bull; Présentez-vous 15 minutes avant l'heure fixée<br>
                &bull; Apportez votre pièce d'identité<br>
                &bull; Munissez-vous de ce document
            </p>
        </div>
        @endif

        <div class="footer">
            <p>labo.dz - Excellence en Diagnostics Biologiques</p>
            <p>Document généré automatiquement le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</p>
        </div>
    </div>
</body>
</html>
