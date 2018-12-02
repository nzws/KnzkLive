const app = require('express')()
const http = require('http').Server(app)
const io = require('socket.io')(http)
const bodyParser = require('body-parser')
let Socket

app.get('/', function(req, res) {
  res.send('ok')
})

app.use(
  bodyParser.urlencoded({
    extended: true
  })
)
app.use(bodyParser.json())

app.post('/send_comment', function(req, res) {
  console.log('[KnzkLive WebSocket] Send Comment', req.body)
  io.emit('knzklive_comment_' + req.body.live_id, req.body)
  res.end()
})

io.on('connection', function(socket) {
  console.log('[KnzkLive WebSocket] connected')
})

http.listen(3000, function() {
  console.log('[KnzkLive WebSocket] listening on *:3000')
})
