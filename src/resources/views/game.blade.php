<!-- resources/views/game/index.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HD2D Side-Scrolling Game with Physics</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        canvas {
            display: block;
            image-rendering: pixelated;
            border: 2px solid #333;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body>
    <canvas id="gameCanvas" width="800" height="400"></canvas>

    <script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const ws = new WebSocket('ws://127.0.0.1:5200');

        let players = {};
        const gravity = 0.5;
        const friction = 0.8;
        const jumpStrength = -10;
        const moveSpeed = 5;

        // Platform layout
        const platforms = [{
                x: 0,
                y: 350,
                width: 800,
                height: 50
            },
            {
                x: 200,
                y: 250,
                width: 100,
                height: 20
            },
            {
                x: 400,
                y: 200,
                width: 100,
                height: 20
            },
            {
                x: 600,
                y: 150,
                width: 100,
                height: 20
            }
        ];

        // Update drawing functions to use colors instead of placeholders
        function drawBackground() {
            ctx.fillStyle = '#87CEEB'; // Sky blue color
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }

        function drawPlatforms() {
            ctx.fillStyle = '#8B4513'; // Saddle brown color for platforms
            platforms.forEach(platform => {
                ctx.fillRect(platform.x, platform.y, platform.width, platform.height);
            });
        }

        function drawPlayer(player) {
            ctx.fillStyle = player.color || 'red';
            ctx.fillRect(player.x, player.y, 20, 40);

            // Draw player name
            ctx.fillStyle = 'white';
            ctx.font = '12px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(player.name, player.x + 10, player.y - 5);
        }

        function drawPlayers() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            drawBackground();
            drawPlatforms();
            for (const id in players) {
                drawPlayer(players[id]);
            }
        }

        let lastUpdateTime = 0;
        const serverUpdateRate = 1000 / 60; // 60 updates per second

        function interpolatePositions(previousState, currentState, alpha) {
            const interpolatedState = {};
            for (const id in currentState) {
                if (previousState[id]) {
                    interpolatedState[id] = {
                        x: previousState[id].x + (currentState[id].x - previousState[id].x) * alpha,
                        y: previousState[id].y + (currentState[id].y - previousState[id].y) * alpha,
                        velocityX: currentState[id].velocityX,
                        velocityY: currentState[id].velocityY,
                        isJumping: currentState[id].isJumping,
                        color: currentState[id].color,
                        name: currentState[id].name
                    };
                } else {
                    interpolatedState[id] = currentState[id];
                }
            }
            return interpolatedState;
        }

        let previousPlayers = {};
        let currentPlayers = {};

        ws.onmessage = (message) => {
            const response = JSON.parse(message.data);

            if (response.type === 'newPlayer' || response.type === 'updatePosition') {
                previousPlayers = {
                    ...currentPlayers
                };
                currentPlayers = response.data.players;
                lastUpdateTime = performance.now();
            } else if (response.type === 'playerLeft') {
                previousPlayers = {
                    ...currentPlayers
                };
                currentPlayers = response.data.players;
            }

            console.log('Received from server:', response);
        };

        function gameLoop(timestamp) {
            const timeSinceUpdate = timestamp - lastUpdateTime;
            const alpha = Math.min(timeSinceUpdate / serverUpdateRate, 1);

            players = interpolatePositions(previousPlayers, currentPlayers, alpha);

            drawPlayers();
            requestAnimationFrame(gameLoop);
        }

        gameLoop(performance.now());

        ws.onopen = () => {
            console.log('WebSocket connection opened');
            const playerName = prompt("Enter your name:");
            const playerColor = prompt("Enter your color (e.g., red, #FF0000):");
            ws.send(JSON.stringify({
                cmd: 'newplayer',
                name: playerName,
                color: playerColor
            }));
        };

        ws.onclose = () => {
            console.log('WebSocket connection closed');
        };

        ws.onerror = (error) => {
            console.log('WebSocket error:', error);
        };



        function movePlayer(direction) {
            ws.send(JSON.stringify({
                cmd: direction
            }));
        }

        window.addEventListener('keydown', (event) => {
            switch (event.key) {
                case 'ArrowLeft':
                    movePlayer('left');
                    break;
                case 'ArrowRight':
                    movePlayer('right');
                    break;
                case 'ArrowUp':
                case ' ':
                    movePlayer('jump');
                    break;
            }
        });

        // onclose
        ws.onclose = () => {
            console.log('WebSocket connection closed');
            // 發送退出訊息給伺服器
            ws.send(JSON.stringify({
                cmd: 'disconnect'
            }));
        };

        // 當玩家關閉瀏覽器或刷新頁面時發送退出訊息
        window.addEventListener('beforeunload', () => {
            // 發送退出訊息給伺服器
            ws.send(JSON.stringify({
                cmd: 'disconnect'
            }));
        });
    </script>

</body>

</html>