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
            background: #7cb342;
            color: #ffffff;
        }
        h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #558b2f;
        }
    </style>
</head>
<body>
    <h2>{{ $page_title }}</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ward Plot No</th>
                <th>Tree No</th>
                <th>Tree Name</th>
                <th>Scientific Name</th>
                <th>Family</th>
                <th>Girth</th>
                <th>Height</th>
                <th>Condition</th>
                <th>Location</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($trees as $tree)
                <tr>
                    <td>{{ $tree->id }}</td>
                    <td>{{ $tree->ward_plot_no }}</td>
                    <td>{{ $tree->tree_no ?? '-' }}</td>
                    <td>{{ $tree->tree->name ?? '-' }}</td>
                    <td>{{ $tree->scientific->scientific_name ?? '-' }}</td>
                    <td>{{ $tree->familyRelation->family_name ?? '-' }}</td>
                    <td>{{ $tree->girth ?? '-' }}</td>
                    <td>{{ $tree->height ?? '-' }}</td>
                    <td>{{ $tree->condition ?? '-' }}</td>
                    <td>{{ $tree->address ?? '-' }}</td>
                    <td>{{ $tree->created_at->format('Y-m-d H:i:s') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center text-muted">No trees found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>