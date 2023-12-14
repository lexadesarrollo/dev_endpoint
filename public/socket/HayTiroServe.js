import Express from "express";
import { readFileSync } from "fs";
import { Server } from "socket.io";
import { createServer } from "node:https";

const port = 3000;

const app = Express();
const options = {
    key: readFileSync("./public/ssl/llave.key"),
    cert: readFileSync("./public/ssl/cert.crt")
  };
const server = createServer(options, app);
const io = new Server(server, {
    cors: { origin: "*" },
    connectionStateRecovery: {},
});

io.on("connection", (socket) => {
    console.log("Connected to chat Hay Tiro");

    socket.on("disconnect", () => {
        console.log("Disconnected from Hay Tiro");
    });

    socket.on("chat message", (msg, user, time) => {
        let data = {
            chat: socket.handshake.auth.chat,
            mensaje: msg,
            user: socket.handshake.auth.user,
            time: time
        };

        let result = postData("https://www.haytiro.mx/api/postMsg", data);
        io.emit("chat message", msg, user, time);
    });

    if (!socket.recovered) {
        try {
            let data = {
                chat: socket.handshake.auth.chat,
            };
            let result = postData(
                "https://www.haytiro.mx/api/getChat",
                data
            ).then((param) => {
                param.forEach((param) => {
                    socket.emit('chat message', param.message, param.id_user, param.time_at);
                });
            });
        } catch (error) {}
    }
});

app.get("/", (req, res) => {
    res.send("Servidor activo.");
});

server.listen(port, () => {
    console.log("Servidor Hay Tiro activo en el puerto " + port);
});

async function postData(url, data) {
    var response = await fetch(url, {
        method: "post",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
    });

    return response.json();
}
