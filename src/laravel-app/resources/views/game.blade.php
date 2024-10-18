<!-- resources/views/game/index.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket Character Movement</title>
    <style>
        canvas {
            background-color: #f0f0f0;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <canvas id="gameCanvas" width="400" height="400"></canvas>

    <script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const ws = new WebSocket('ws://127.0.0.1:5200'); // 假設你的 WebSocket 服務在這個地址

        let player = {
            x: 200,
            y: 200,
            size: 20,
            color: 'blue'
        };

        function drawPlayer() {
            ctx.clearRect(0, 0, canvas.width, canvas.height); // 清空畫布
            ctx.fillStyle = player.color;
            ctx.fillRect(player.x, player.y, player.size, player.size); // 繪製角色
        }

        function movePlayer(direction) {
            switch (direction) {
                case 'left':
                    player.x -= 10;
                    break;
                case 'right':
                    player.x += 10;
                    break;
                case 'up':
                    player.y -= 10;
                    break;
                case 'down':
                    player.y += 10;
                    break;
            }
            drawPlayer();
            ws.send(JSON.stringify({ cmd: direction })); // 發送 WebSocket 消息
        }

        window.addEventListener('keydown', (event) => {
            switch (event.key) {
                case 'ArrowUp':
                    movePlayer('up');
                    break;
                case 'ArrowDown':
                    movePlayer('down');
                    break;
                case 'ArrowLeft':
                    movePlayer('left');
                    break;
                case 'ArrowRight':
                    movePlayer('right');
                    break;
            }
        });

        ws.onopen = () => {
            console.log('WebSocket connection opened');
        };

        ws.onmessage = (message) => {
            const data = JSON.parse(message.data);
            console.log('Received from server:', data);
        };

        ws.onclose = () => {
            console.log('WebSocket connection closed');
        };

        ws.onerror = (error) => {
            console.log('WebSocket error:', error);
        };

        drawPlayer(); // 初始化角色繪製
    </script>
</body>
</html>
