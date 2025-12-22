<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Communicatieblad</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 13px;
            color: #333;
            line-height: 1.5;
        }

        /* HEADER STYLING */
        .header-container {
            width: 100%;
            border-bottom: 2px solid #e11d48; /* De primaire kleur (roze/rood van Filament) */
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header-table {
            width: 100%;
        }
        .title {
            font-size: 24px;
            font-weight: 800;
            color: #111;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .meta-box {
            text-align: right;
        }
        .meta-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #999;
            font-weight: bold;
        }
        .meta-value {
            font-size: 14px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
        }

        /* TABEL STYLING */
        .content-table {
            width: 100%;
            border-collapse: collapse;
        }
        .content-table th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
            padding-left: 8px;
        }
        .content-table td {
            vertical-align: top;
            padding: 12px 8px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        /* KOLOM SPECIFIEK */
        .col-date { 
            width: 70px; 
            color: #666;
            font-size: 12px;
        }
        .col-room { 
            width: 60px; 
        }
        .room-badge {
            background-color: #f3f4f6;
            color: #1f2937;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
            border: 1px solid #e5e7eb;
            display: inline-block;
        }
        .col-name { 
            width: 180px; 
            font-weight: bold; 
            color: #000;
            font-size: 14px;
        }
        .col-note {
            color: #333;
            font-style: italic;
        }

        /* HANDIG VOOR LEGE NOTITIES */
        .empty-note-line {
            border-bottom: 1px dotted #ccc;
            height: 20px;
            width: 100%;
            margin-bottom: 8px;
            display: block;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #aaa;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header-container">
        <table class="header-table">
            <tr>
                <td style="vertical-align: bottom;">
                    <div class="subtitle">Communicatieblad</div>
                    <h1 class="title">{{ $houseName }}</h1>
                </td>
                <td class="meta-box" style="vertical-align: bottom;">
                    <div>
                        <span class="meta-label">T.a.v.</span><br>
                        <span class="meta-value">{{ $headerRecipient }}</span>
                    </div>
                    <div style="margin-top: 10px;">
                        <span class="meta-label">Datum</span><br>
                        <span class="meta-value">
                            {{ \Carbon\Carbon::parse($headerDate)->format('d/m/Y') }} 
                            <span style="font-weight: normal; color: #666;">{{ $headerMoment }}</span>
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="content-table">
        <thead>
            <tr>
                <th>Datum</th>
                <th>Kmr</th>
                <th>Bewoner</th>
                <th>Notitie / Opmerking</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                @php
                    $displayDate = $row['date'] 
                        ? \Carbon\Carbon::parse($row['date'])->format('d/m') 
                        : '-';
                @endphp
                <tr>
                    <td class="col-date">
                        {{ $displayDate }}
                    </td>

                    <td class="col-room">
                        <span class="room-badge">{{ $row['room_number'] }}</span>
                    </td>

                    <td class="col-name">
                        {{ $row['name'] }}
                    </td>

                    <td class="col-note">
                        @if(!empty($row['note']))
                            {!! nl2br(e($row['note'])) !!}
                        @else
                            <span class="empty-note-line"></span>
                            <span class="empty-note-line"></span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Gegenereerd op {{ date('d-m-Y H:i') }} • Pagina <span class="page-number"></span>
    </div>

</body>
</html>