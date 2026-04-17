<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $page_title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f0f0f0;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <h2>{{ $page_title }}</h2>
    <table>
        <thead>
            <tr>
                <th>Project Name</th>
                <th>Client Name</th>
                <th>State</th>
                <th>Company</th>
                <th>Created</th>
                <th>Officer</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($projects as $project)
                <tr>
                    <td>{{ $project->project_name }}</td>
                    <td>{{ $project->client_name ?? '-' }}</td>
                    <td>{{ $project->state->state_name ?? '-' }}</td>
                    <td>{{ $project->company_name ?? '-' }}</td>
                    <td>{{ $project->created_at->format('Y-m-d') }}</td>
                    <td>{{ $project->fieldOfficer->name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
