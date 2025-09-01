const WebSocket = require('ws');
const http = require('http');

const server = http.createServer();
const wss = new WebSocket.Server({ server });

wss.on('connection', (ws) => {
    console.log('Client connected');
    
    ws.on('message', (message) => {
        console.log('Received:', message.toString());
    });

    ws.on('close', () => {
        console.log('Client disconnected');
    });
});

// API endpoint для broadcast
server.on('request', (req, res) => {
    if (req.method === 'POST' && req.url === '/broadcast') {
        let data = '';
        
        req.on('data', chunk => {
            data += chunk.toString();
        });
        
        req.on('end', () => {
            try {
                const broadcastData = JSON.parse(data);
                
                // Отправка всем подключенным клиентам
                wss.clients.forEach(client => {
                    if (client.readyState === WebSocket.OPEN) {
                        client.send(JSON.stringify({
                            channel: broadcastData.channel,
                            event: broadcastData.event,
                            data: broadcastData.data
                        }));
                    }
                });
                
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ status: 'success' }));
            } catch (error) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: 'Invalid JSON' }));
            }
        });
    } else {
        res.writeHead(404);
        res.end();
    }
});

server.listen(3001, () => {
    console.log('WebSocket server running on port 3001');
});