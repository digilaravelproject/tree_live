<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tree Inventory Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #444;
            padding: 6px 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .meta {
            margin-bottom: 15px;
            font-size: 12px;
            line-height: 1.8;
        }
        .meta td {
            border: none;
            padding: 2px 10px;
        }
        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Tree Inventory Report</h2>
    </div>
    <table class="meta" style="width: auto; margin-bottom: 20px;">
        <tr>
            <td><strong>Project Name</strong></td>
            <td>{{ $project->project_name }}</td>
        </tr>
        <tr>
            <td><strong>Client Name</strong></td>
            <td>{{ $project->client_name }}</td>
        </tr>
        <tr>
            <td><strong>Project By</strong></td>
            <td>{{ $projectBy }}</td>
        </tr>
        <tr>
            <td><strong>Date</strong></td>
            <td>{{ date('m/d/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Location</strong></td>
            <td>{{ $location }}</td>
        </tr>
        <tr>
            <td><strong>Address</strong></td>
            <td>{{ $address }}</td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>Sr. No</th>
                <th>Tree Name</th>
                <th>Tree Count</th>
            </tr>
        </thead>
        <tbody>
            @php
                $srNo = 1;
                $totalCount = 0;
                $grouped = $trees->groupBy('tree_name');
            @endphp
            @foreach ($grouped as $treeNameId => $group)
                @php
                    $tName = \App\Models\Tree::find($treeNameId);
                    $count = $group->count();
                    $totalCount += $count;
                @endphp
                <tr>
                    <td>{{ $srNo++ }}</td>
                    <td>{{ $tName->name ?? '-' }}</td>
                    <td>{{ $count }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2"><strong>Total Trees</strong></td>
                <td><strong>{{ $totalCount }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>