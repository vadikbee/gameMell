 # Blade шаблон для игры

 <!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <title>Game</title>
    <script>
        window.gameConfig = {
            token: "{{ $token }}",
            userId: {{ $userId }},
            balance: {{ $balance }},
            currency: "{{ $currency }}",
            apiUrl: "{{route('game.callback') }}"
        };
    </script>
    @vite(['resources/js/game.js']) <!-- Подключение Vue/Nuxt приложения -->
</head>
<body>
    <div id="app"></div>
</body>
</html>