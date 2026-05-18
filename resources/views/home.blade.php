<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form action="{{ route('editpair') }}" method="post">
        @csrf
        <input type="text" name="pair" placeholder="pair">
        <button type="submit">Test Order</button>
    </form>
</body>

</html>
