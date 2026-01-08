<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

<h2>Login</h2>

@if ($errors->any())
    <div style="color:red;">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST" action="{{ route('login.post') }}">
    @csrf

    <div>
        <label>E-mail</label><br>
        <input type="email" name="email" required>
    </div>

    <br>

    <div>
        <label>Senha</label><br>
        <input type="password" name="password" required>
    </div>

    <br>

    <button type="submit">Entrar</button>
</form>

</body>
</html>
