<?php
session_start();
// echo @$_SESSION['usuario'];die;
if(!isset($_POST['dados'])) {
  header('Location: index.php');die;
}

$dados = $_POST['dados'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//BR" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
<html lang="br">
  <head>
    <title>Socket.IO chat</title>
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        outline: 0;
      }
      body {
        font: 13px Helvetica, Arial;
        background: rgba(0,0,0,0.8);
      }
      form {
        background: #000;
        padding: 10px 30px;
        position: fixed;
        bottom: 0;
        width: 100%;
        display: flex;
      }
      form input {
        border: 0;
        padding: 10px;
        width: 90%;
        margin-right: 0.5%;
        border-radius: 10px;
      }
      form button {
        width: 9%;
        background: #3498db;
        color: #fff;
        border: none;
        padding: 10px;
        border-radius: 10px;
        font-weight: bold;
      }
      #messages {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        width: 800px;
        font-size: 16px;
        margin: auto;
        margin-bottom: 40px;
        margin-top: 10px;
        color: #444;
      }
      #messages > .right{
        align-items: flex-end;
      }
      #messages > .left{
        align-items: flex-start;
      }
      #messages li {
        padding: 5px 10px;
      }
      #messages li:nth-child(odd) {
        /* background: #eee; */
      }
      .left {
        text-align: left;
        background: #dadada;
        align-self: flex-start;
      }
      .right {
        text-align: right;
        background: #fff;
        align-self: flex-end;
      }
      .message {
        max-width: 800px;
        /* width: 500px; */
        /* height: 100px; */
        display: flex;
        flex-direction: column;
        /* align-self: stretch; */
        /* justify-content: space-between; */
        /* display: inline-block; */
        
        padding: 10px 20px;
        border-radius: 10px;
        margin-bottom: 50px;
      }
      .user {
        font-size: 15px;
        font-weight: bold;
      }
      .right .time {
        font-size: 10px;
        text-align: right;
      }
      .left .time {
        font-size: 10px;
        text-align: left;
      }
      .goBack {
        color: #fff;
        font-weight: bold;
        font-size: 20px;
        cursor: pointer;
      }
      .title {
        color: #fff;
        font-weight: bold;
        font-size: 20px;
        /* padding: 16px 0 10px 0; */
      }
      .subtitle { 
        color: #f5f5f5;
        /* font-weight: bold; */
        font-size: 14px;
        /* padding: 24px; */
        text-align: center;
        padding-bottom: 10px;
      }
      .navbar {
        height: 70px;
        background: rgba(0,0,0,0.9);
        width: 100%;
        
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: sticky;
        top: 0;
      }
      .goBack {
        position: fixed;
        top:0;
        left: 0;
        padding: 22px 50px;
      }
      .spanMessage {
        display: block;
        word-wrap:break-word;
        max-width: 750px;
        white-space: normal 
      }
      .new {
        /* align-self: stretch; */
        display: flex;
        flex: 1;
        flex-direction: column;
        justify-content: space-between;
        min-height: 64px;
      }

      .new img {
        max-width: 500px;
        max-height: 500px;
      }

      #file {
        display: none;
      }
      .labelFile {
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #3498db;
        border-radius: 10px;
        color: #fff;
        cursor: pointer;
        /* margin: 10px; */
        padding: 5px 10px;
        margin-right: 10px;
      }

    </style>
  </head>
  <body>
    <div class="navbar">
      <span class="goBack" onClick="handleGoBack()">
        Sair
      </span>
      <h1 class="title">
        Sala de <?=ucwords(str_replace('.',' ', $dados));?>
      </h1>
      <h6 class="subtitle">
        Sistemas
      </h6>
    </div>
    <div id="messages"></div>
    <form action="">
      <label for='file' class='labelFile'>Arquivos</label>
      <input id="file" type="file" />
      <input id="m" autocomplete="off" placeholder="Digite a sua mensagem..." /><button>Enviar</button>
    </form>
    <script src="js/socket.io.js"></script>
    <script src="js/jquery-3.3.1.js"></script>
    <script>
      
      // $(function () {
        const roomSelected = '<?=$dados;?>';
        let client = '<?=ucwords(str_replace('.',' ', @$_SESSION['usuario'])); ?>';
        let nameAdm = '<?=ucwords(str_replace('.',' ', $dados)); ?>';
        var socket = io('192.168.3.4:3000/');

        $("#m").focus();

        // document.getElementById('file').addEventListener('change', handleChangeFile);

        // function handleChangeFile(e) {
        //   // var output = document.getElementById("output");

        //   const files = $("#file")[0].files;
        //   for (var i = 0; i < files.length; i++) {
        //     const prevImage = window.URL.createObjectURL(files[i]);
        //     const name = files[i].name;
        //     // console.log(prevImage);
        //     // output.href = prevImage;
        //     // $(".anexos").append(`<a href=${prevImage}>${name}</a>`);
            
        //     socket.emit("set room image", {
        //       name: name,
        //       file: prevImage,
        //       user: client,
        //       room: roomSelected,
        //     });
        //   }
          
        // }

        socket.emit('subscribeToRoom', roomSelected);

        $("form").submit(function () {
          //caso envie imagens
          let anexos = false;

          if($("#file").val()) {
            const files = $("#file")[0].files;
            for (var i = 0; i < files.length; i++) {
              const prevImage = window.URL.createObjectURL(files[i]);
              const name = files[i].name;
              // console.log(prevImage);
              // output.href = prevImage;
              // $(".anexos").append(`<a href=${prevImage}>${name}</a>`);
              
              socket.emit("set room", {
                message: $("#m").val(),
                anexos: prevImage,
                user: client,
                room: roomSelected,
              });
            }

            $("#file").val('');
            $("#m").val("");
            $("#m").focus();
            return false;
          }

          socket.emit("set room", {
            message: $("#m").val(),
            anexos: anexos,
            user: client,
            room: roomSelected,
          });

          $("#m").val("");
          $("#m").focus();
          return false;
        });

        socket.on("chat message", (data) => {
          // confirm('hi')
          const {message, anexo, user} = data;
          const yourMessage = user === client ? 'right' : 'left';
          
          const date = new Date()
          const dia = date.getDate();
          let mes = (date.getMonth() + 1);
          mes = (mes < 10 ? `0${mes}`: mes);
          const ano = date.getFullYear();
          const hour = (date.getHours() < 10 ? `0${date.getHours()}` : date.getHours());
          const min = (date.getMinutes() < 10 ? `0${date.getMinutes()}` : date.getMinutes());
          const seq = (date.getSeconds() < 10 ? `0${date.getSeconds()}` : date.getSeconds());

          const time = `${dia}/${mes}/${ano} ${hour}:${min}:${seq}` ;
          
          if(anexo) {
            $("#messages")
              .append($(`<div class="${yourMessage} message">`)
              .html(`<div class='new'><span class='user'>${user}</span> <span class='spanMessage'><img src='${anexo}' /><br>${message}</span> <span class='time'>${time}</span></div>`));
          } else {
            $("#messages")
            .append($(`<div class="${yourMessage} message">`)
            // .html(`<span class='user'>${user}</span> <span class='spanMessage'>${message}</span> <span class='time'>${time}</span>`));
            .html(`<div class='new'><span class='user'>${user}</span> <span class='spanMessage'>${message}</span> <span class='time'>${time}</span></div>`));
          }
          
          window.scrollTo(0, document.body.scrollHeight);
        });
      // });


      function handleGoBack() {
        // socket.emit("unsubscribeToRoom", roomSelected);
        // window.history.back();
        location.reload();
      }


      /**
       * mensagens padrão para os usuários
       */
      if(nameAdm !== client) {
        socket.emit("set room", {
          message: 'Bom dia, tudo bem?',
          user: nameAdm,
          room: roomSelected,
        });
        socket.emit("set room", {
          message: 'Em que posso ajudar?',
          user: nameAdm,
          room: roomSelected,
        });
      }
    </script>
  </body>
</html>
