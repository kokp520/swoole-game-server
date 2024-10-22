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

    let players = {}; // 保存所有玩家的狀態

    function drawPlayers() {
        ctx.clearRect(0, 0, canvas.width, canvas.height); // 清空畫布
        for (const id in players) {
            const player = players[id];
            ctx.fillStyle = player.color;
            ctx.fillRect(player.x, player.y, player.size || 20, player.size || 20); // 繪製玩家
        }
    }

    function movePlayer(direction) {
        ws.send(JSON.stringify({ cmd: direction })); // 發送移動命令
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
        const response = JSON.parse(message.data);

        if (response.type === 'newPlayer' || response.type === 'updatePosition') {
            players = response.data.players; // 更新所有玩家的狀態
            drawPlayers(); // 繪製所有玩家
        } else if (response.type === 'playerLeft') {
            players = response.data.players; // 更新狀態
            drawPlayers(); // 重新繪製
        }

        console.log('Received from server:', response);
    };

    ws.onclose = () => {
        console.log('WebSocket connection closed');
    };

    ws.onerror = (error) => {
        console.log('WebSocket error:', error);
    };
</script>

</body>
</html>
