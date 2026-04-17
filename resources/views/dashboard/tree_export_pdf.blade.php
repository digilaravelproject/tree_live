<!DOCTYPE html>
<html>
<head>
    <title>{{ $page_title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>{{ $page_title }}</h2>
    <table>
        <thead>
            <tr>
                <th>Tree No</th>
                <th>Project</th>
                <th>Ward</th>
                <th>Common Name</th>
                <th>Scientific Name</th>
                <th>Girth</th>
                <th>Height</th>
                <th>Condition</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($trees as $tree)
            <tr>
                <td>{{ $tree->tree_no }}</td>
                <td>{{ $tree->project->project_name ?? '-' }}</td>
                <td>{{ $tree->ward_plot_no ?? '-' }}</td>
                <td>{{ $tree->tree->name ?? $tree->tree_name }}</td>
                <td>{{ $tree->scientific->scientific_name ?? $tree->scientific_name }}</td>
                <td>{{ $tree->girth }}</td>
                <td>{{ $tree->height }}</td>
                <td>{{ $tree->condition }}</td>
                <td>{{ $tree->created_at->format('d-m-Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>