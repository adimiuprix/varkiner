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
        <input type="text" name="pair" value="{{ $pair->pair }}" placeholder="pair">

        <select name="side">
            <option value="buy" {{ $pair->side == 'buy' ? 'selected' : '' }}>BUY</option>
            <option value="sell" {{ $pair->side == 'sell' ? 'selected' : '' }}>SELL</option>
        </select>
        <button type="submit">Order</button>
    </form>
</body>

</html>
