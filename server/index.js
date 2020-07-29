var app = require("express")();
var http = require("http").Server(app);
var io = require("socket.io")(http);
var port = process.env.PORT || 3000;

// let countRooms = [];

app.get("/", function (req, res) {
  res.sendFile(__dirname + "/index.html");
  // res.sendFile("http://portal.jljempresas.com.br/whtstd/SocketIo/index.html");
});

io.on("connection", function (socket) {
  socket.on("subscribeToRoom", function (room) {
    // console.log("joining room", room);
    // const contador = !countRooms[room] ? 1 : countRooms[room] + 1;
    // countRooms[room] = contador;
    // console.log(countRooms[room]);
    // countRooms[room]++;

    // console.log(room);

    socket.join(room);
  });

  // evento de verificação das salas
  socket.on("verifyAdm", function (room) {
    // if() {
    // } else {
    // }
    // socket.emit("verifyRoom", event.target.id);
  });

  socket.on("status", function (room) {
    // if() {
    // } else {
    // }
    socket.emit("responseStatus");
  });

  socket.on("verifyRoom", function (room, adm) {
    // console.log(socket.adapter.rooms[room]);

    // if (countRooms[room] && countRooms[room] === 2) {
    if ((socket.adapter.rooms[room] && adm) || socket.adapter.rooms[room]) {
      if (socket.adapter.rooms[room].length === 2) {
        socket.emit("responseVerifyRoom", {
          response: false,
          admValidation: true,
          room,
        });
      } else {
        // verificacao do administrador
        socket.emit("responseVerifyRoom", {
          response: true,
          admValidation: true,
          room,
        });
      }
    } else if (adm) {
      socket.emit("responseVerifyRoom", {
        response: true,
        admValidation: true,
        room,
      });
    } else {
      socket.emit("responseVerifyRoom", {
        response: false,
        admValidation: false,
        room,
      });
    }
  });

  socket.on("unsubscribeToRoom", function (room) {
    // console.log("leaving room", room);
    // const contador = countRooms[room] - 1;
    // countRooms[room] = contador;
    // console.log(countRooms[room]);
    socket.leave(room);
  });

  socket.on("set room", (data) => {
    const { message, anexos, user, room } = data;

    let anexo = false;

    if (anexos) {
      anexo = anexos;
    }
    // console.log("sending room post", room);
    // io.join(room);
    // console.log(socket.adapter.rooms[room].length);
    io.sockets.in(room).emit("chat message", {
      message: message,
      anexo: anexo,
      user: user,
    });
  });

  socket.on("disconnecting", () => {
    // const [, room] = Object.keys(socket.rooms);
    // console.log(room);
    // const contador = countRooms[room] - 1;
    // countRooms[room] = contador;
    // console.log(countRooms[room]);
    // the rooms array contains at least the socket ID
  });

  // atualizar a pagina inicial de todos
  setInterval(() => {
    let allRooms = Object(socket.adapter.rooms);

    // const data = allRooms.forEach((room) => ({
    //   room: room,
    //   status:
    //     (socket.adapter.rooms[room] && socket.adapter.rooms[room].length > 0
    //       ? "livre"
    //       : "ausente") || "ocupado",
    // }));

    // const data = allRooms.forEach((room) => console.log(room));

    // console.log(rooms); // [ <socket.id>, 'room 237' ]
    // console.log(allRooms);
    // console.log(Object.keys(socket.adapter.rooms));
    socket.emit("status", { data: allRooms });
  }, 5000);
});

http.listen(port, function () {
  console.log("listening on *:" + port);
});
