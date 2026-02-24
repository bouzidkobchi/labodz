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
            font-family: 'Helvetica', 'Arial', sans-serif;
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
    </style>
</head>
</head>

<body>
    <div class="page-container">
        <div class="header">
            @if($barcode)
                <div style="float: right; text-align: center;">
                    <img src="{{ $barcode }}" width="120" alt="QR Code" style="display: block; margin-bottom: 5px;">
                    <span style="font-size: 10px; color: #666; font-weight: bold; text-transform: uppercase;">Medical Visit QR</span>
                </div>
            @endif
            <h1 style="text-align: left;">labo.dz</h1>
            <p style="text-align: left;">Expertise en Analyses Médicales & Diagnostics</p>
            <p style="text-align: left;">Tel: 0550 12 34 56 | E-mail: info@labo-dz.com</p>
        </div>

        <div class="doc-badge">
            CONFIRMATION DE RENDEZ-VOUS
        </div>

        <div class="section" style="margin-top: 40px;">
            <div class="section-title">Informations du Patient</div>
            <div class="info-card">
                <table class="info-table">
                    <tr>
                        <td class="label">Nom Complet:</td>
                        <td class="value">{{ $reservation->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Date du RDV:</td>
                        <td class="value">{{ \Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }} à {{ $reservation->time }}</td>
                    </tr>
                    <tr>
                        <td class="label">Sexe:</td>
                        <td class="value">{{ $reservation->gender == 'male' ? 'Masculin' : 'Féminin' }}</td>
                    </tr>
                    <tr>
                        <td class="label">N° de Dossier:</td>
                        <td class="value">#{{ $reservation->id }}</td>
                    </tr>
                </table>
            </div>
        </div>

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
                    @foreach($reservation->analyses as $analysis)
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

        @php
        $hasPreparation = $reservation->analyses->filter(function($analysis) {
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

            @foreach($reservation->analyses as $analysis)
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

        <div class="important-notes" style="margin-top: 40px; border-top: 1px dashed #cbd5e0; padding-top: 20px;">
            <p style="font-size: 10pt; color: #4a5568;">
                &bull; Présentez-vous 15 minutes avant l'heure fixée<br>
                &bull; Apportez votre pièce d'identité<br>
                &bull; Munissez-vous de ce document
            </p>
        </div>

        <div class="footer">
            <p>labo.dz - Excellence en Diagnostics Biologiques</p>
            <p>Document généré automatiquement le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}</p>
        </div>
    </div>
</body>
</html>
