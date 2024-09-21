<!DOCTYPE html>
<html>
<head>
    <title>Medalists</title>
</head>
<body>
    <h1>Medalists List</h1>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Medal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($medalists as $medalist)
                <tr>
                    <td>{{ $medalist['name'] }}</td>
                    <td>{{ $medalist['medal'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $medalists->links() }} <!-- This will render pagination links -->
</body>
</html>
