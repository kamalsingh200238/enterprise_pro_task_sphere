<!DOCTYPE html>
<html>
<head>
    <title>Logs Export</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        /* base styles for pdf optimization */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8px; /* small base font size to fit more content */
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }

        /* table and borders */
        table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 4px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        /* Column width distribution (adjust percentages as needed) */
        th:nth-child(1), td:nth-child(1) { width: 10%; } /* Timestamp */
        th:nth-child(2), td:nth-child(2) { width: 15%; } /* Caused by */
        th:nth-child(3), td:nth-child(3) { width: 10%; } /* Event */
        th:nth-child(4), td:nth-child(4) { width: 15%; } /* Subject */
        th:nth-child(5), td:nth-child(5) { width: 25%; } /* Previous Values */
        th:nth-child(6), td:nth-child(6) { width: 25%; } /* New Values */

        /* prevent page breaks inside rows */
        tr {
            page-break-inside: avoid;
        }

        /* value containers */
        .value-container {
            max-height: 80px;
            overflow: hidden;
        }

        /* allow text to wrap and prevent truncation */
        .wrap-text {
            white-space: normal; /* allow text to wrap */
            word-wrap: break-word; /* break words if necessary */
            word-break: break-word; /* ensure long words also break properly */
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1 class="text-center" style="font-size: 10px; margin-bottom: 10px;">Activity Logs</h1>
    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Caused by</th>
                <th>Event</th>
                <th>Subject</th>
                <th>Previous Values</th>
                <th>New Values</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $activityLog)
                <tr>
                    <td>{{ $activityLog['timestamp'] }}</td>

                    <td class="wrap-text">
                        {{ $activityLog['causer']['name'] }} ({{ $activityLog['causer']['email'] }})
                    </td>

                    <td>{{ $activityLog['event'] }}</td>

                    <td class="wrap-text">
                        <div>{{ $activityLog['subject']['heading'] }}</div>
                        <div>{{  $activityLog['subject']['description']  }}</div>
                    </td>

                    <td>
                        <div class="value-container">
                            @foreach($activityLog['oldValues'] as $value)
                                <div class="wrap-text" title="{{ $value }}">
                                    {{ $value }}
                                </div>
                            @endforeach
                        </div>
                    </td>

                    <td>
                        <div class="value-container">
                            @foreach($activityLog['newValues'] as $value)
                                <div class="wrap-text" title="{{ $value }}">
                                    {{ $value }}
                                </div>
                            @endforeach
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="text-center" style="font-size: 8px; color: #888; margin-top: 10px;">
        Generated on {{ now()->format('d M Y, H:i:s') }}
    </div>
</body>
</html>
