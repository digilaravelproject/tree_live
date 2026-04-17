<!DOCTYPE html>
<html>
<head>
    <title>{{ $privacy->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h2 {
            margin-bottom: 10px;
            color: #7cb342;
        }
        hr {
            margin: 20px 0;
            border-color: #7cb342;
        }
        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th,
        table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #7cb342;
            color: white;
        }
        /* Ensure colors and borders print correctly */
        @media print {
            body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            table th {
                background-color: #7cb342 !important;
                color: white !important;
            }
            h2 {
                color: #7cb342 !important;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <h2>{{ $privacy->title }}</h2>
    <hr>
    <div>{!! $privacy->content !!}</div>
</body>
</html>