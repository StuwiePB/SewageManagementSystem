<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operator Dashboard</title>
    @include('partials.favicon')
</head>
<body>
    <h1>BOO this is operator</h1>
    <form method="POST" action="{{ route('logout') }}" style="margin-top: 1rem;">
        @csrf
        <button type="submit">End session (log out)</button>
    </form>
</body>
</html>
