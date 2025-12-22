<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Communicatieblad</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 14px;
            color: #000;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            font-size: 18px;
            text-decoration: underline;
            margin: 0 0 5px 0;
            font-weight: bold;
        }
        .sub-header {
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 5px;
        }
        .meta-date {
            font-size: 15px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            vertical-align: top;
            padding-bottom: 15px;
        }
        .col-date {
            width: 80px;
            font-weight: normal;
            color: #333;
        }
        .col-room {
            width: 60px;
            font-weight: bold;
        }
        .col-name {
            width: 200px;
            font-weight: bold;
            padding-right: 10px;
        }
        .col-note {
            color: #000;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Communicatieblad {{ $houseName }}</h1>
        <div class="sub-header">T.a.v. {{ $headerRecipient }}</div>
        <div class="meta-date">
            {{ \Carbon\Carbon::parse($headerDate)->format('d/m/Y') }} {{ $headerMoment }}
        </div>
    </div>

    <table>
        @foreach($rows as $row)
            @php
                $displayDate = $row['date'] 
                    ? \Carbon\Carbon::parse($row['date'])->format('d/m') 
                    : '';
            @endphp
            <tr>
                <td class="col-date">{{ $displayDate }}</td>
                <td class="col-room">{{ $row['room_number'] }}</td>
                <td class="col-name">{{ $row['name'] }}:</td>
                <td class="col-note">{!! nl2br(e($row['note'])) !!}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>