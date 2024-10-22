<!-- resources/views/lobby/index.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lobby</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        #lobbyContainer {
            text-align: center;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        input {
            padding: 10px;
            margin-top: 10px;
            width: 200px;
        }

        button {
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <div id="lobbyContainer">
        <h2>Enter Lobby</h2>
        <input type="text" id="playerName" placeholder="Enter your name" />
        <br />
        <button id="enterGame">Enter Game</button>
        <br />
        <input type="text" id="customCommand" placeholder="Enter custom command" />
        <button id="enterCommand">Enter Command</button>
    </div>

    <script>
        const ws = new WebSocket('ws://127.0.0.1:5200/lobby'); // 連接到你的 Lobby WebSocket 服務

        document.getElementById('enterGame').addEventListener('click', () => {
            const playerName = document.getElementById('playerName').value;

            if (playerName) {
                ws.send(JSON.stringify({
                    cmd: playerName
                })); // 發送 WebSocket 消息
                // ws.send(JSON.stringify({ type: 'join', name: playerName })); // 傳送玩家名稱到 WebSocket 服務
            } else {
                alert("Please enter your name.");
            }
        });

        document.getElementById('enterCommand').addEventListener('click', () => {
            const customCommand = document.getElementById('customCommand').value;

            if (customCommand) {
                ws.send(JSON.stringify({
                    cmd: customCommand
                })); // 發送 WebSocket 消息
                // ws.send(JSON.stringify({ type: 'join', name: playerName })); // 傳送玩家名稱到 WebSocket 服務
            } else {
                alert("Please enter your name.");
            }
        });

        ws.onopen = () => {
            console.log('Lobby WebSocket connection opened');
        };


        ws.onmessage = (message) => {
            const data = JSON.parse(message.data);
            console.log('Received from server:', data);
        };

        ws.onclose = () => {
            console.log('Lobby WebSocket connection closed');
        };

        ws.onerror = (error) => {
            console.log('WebSocket error:', error);
        };
    </script>

</body>

</html>