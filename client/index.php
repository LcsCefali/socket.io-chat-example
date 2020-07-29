<?php
session_start();
// echo @$_SESSION['usuario'];die;
if(!isset($_SESSION['usuario'])) {
  header('Location: http://portal.jljempresas.com.br/nova_home/');die;
}

$user = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Salas de conversação</title>
  <script src="js/jquery-3.3.1.js"></script>
  <script src="js/socket.io.js"></script>
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
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }
    .setor{
      padding: 24px;
    }
    .setor h1 {
      margin: 15px 0;
      color: #fff;
      border-bottom: 2px solid rgba(255,255,255,0.5);
      border-radius: 2px;
      display: inline-block;
    }
    .setor > div {
      display: grid;
      grid-template-columns: 250px 250px;
      gap: 40px;
    }

    /* .dados {
      padding: 20px;
      display: grid;
      grid-template-columns: 250px 250px;
      gap: 40px;
    } */
    .dados button {
      padding: 20px;
      font-weight: bold;
      text-align: center;
      font-size: 20px;
      cursor: pointer;
      background: rgba(0,0,0,0.8);
      outline: 0;
      /* border: 1px solid #fff; */
      border-radius: 10px;
      color: #eee;
    }
    .livre {
      border: 2px solid #6CAE75;
    }
    .ocupado {
      border: 2px solid #f02;
    }
    .ausente {
      border: 2px solid gold;
    }
    .footer{
      position: fixed;
      bottom: 0;
      right: 0;
      padding: 150px;
      /* display: flex; */
      /* flex-direction: row; */
      /* align-items: flex-end; */
      /* justify-content: space-between; */
    }
    .status {
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: flex-start;
      font-size: 18px;
      color: #fff;
      padding: 5px 0;
    }
    .quadradoLivre{
      border-radius: 10px;
      width:20px;
      height:20px;
      background: #6CAE75;
      margin-right: 8px;
    }

    .quadradoAusente{
      border-radius: 10px;
      width:20px;
      height:20px;
      background: gold;
      margin-right: 8px;
    }

    .quadradoOcupado{
      border-radius: 10px;
      width:20px;
      height:20px;
      background: #f02;
      margin-right: 8px;
    }
  </style>

</head>
<body>
  <div class="navbar"></div>
  <div class="dados">
    <div class="setor">
      <h1 class="title">Sistemas</h1>
      <div>
        <button id="rafael.sai">Rafael Sai</button>
        <button id="lucas.almeida">Lucas Almeida</button>
        <button id="eric.ferrioli">Eric Ferrioli</button>
        <button id="diego.damasceno">Diego Damasceno</button>
        <button id="rafael.carvalho">Rafael Carvalho</button>
        <button id="anselmo.dias">Anselmo Dias</button>
      </div>
    </div>
    <div class="setor">
      <h1 class="title">Help Desk</h1>
      <div>
        <button id="diego.ferreira">Diego Ferreira</button>
        <button id="fernando.cruz">Fernando Cruz</button>
        <button id="gustavo.risso">Gustavo Risso</button>
        <button id="diego.malinsky">Diego Malinsky</button>
      </div>
    </div>
  </div>
  <div class="footer">
    <div class="status">
      <div class="quadradoLivre"></div>
      Livre
    </div>
    <div class="status">
      <div class="quadradoAusente"></div>
      Ausente
    </div>
    <div class="status">
      <div class="quadradoOcupado"></div>
      Ocupado
    </div>
  </div>
</body>
  <script defer>
    var socket = io('192.168.3.4:3000/');
    let data = document.querySelectorAll("button");
    let user = '<?=$user;?>';

    socket.on('status', (value) => {
      
      // data.forEach(element => {
        let status = [];
        for (var property in value.data){
          
          // console.log(document.getElementById(property), document.getElementById('lucas.almeida'), property);
          // console.log(property);
          const user = document.getElementById(property);
          // status.push();
          if(user) {
            // console.log(user);
            if(value.data[property].length > 0 && value.data[property].length < 2) {
              user.classList.remove('ocupado', 'ausente');
              user.classList.add('livre');
            } else if(value.data[property].length === 2) {
              user.classList.remove('livre', 'ausente');
              user.classList.add('ocupado');
            } else {
              user.classList.remove('ocupado', 'livre');
              user.classList.add('ausente');
            }
            
          }
        }
        // Object.keys(value.data).forEach(values => {
        // console.log(values);
        
        // if(element.id ===) {

        // }
        // console.log(element);
        // if() {
        //   element.classList.add('livre');
        // } else if() {
        //   element.classList.add('ausente');
        // } else{
        //   element.classList.add('ocupado');
        // }
        // element.addEventListener('click', handleSelectedRoom);
      // });
    });

    data.forEach(element => {
      element.addEventListener('click', handleSelectedRoom);
      element.classList.add('ausente');
    });

    function handleSelectedRoom(event) {
      const room = event.target.id;
      let isAdm = false;
      //verificar se e adm para parametro
      if(user === room) {
        isAdm = true;
      }

      //verificar quantidade de pessoas na sala
      socket.emit('verifyRoom', room, isAdm);
    }

    //caso não possua adm, atualiza tela
    socket.on('responseVerifyAdm', (value) => {
      let room = document.getElementById(value.room);
      room.classList.add('ausente');
      room.classList.remove('livre');
      return false;
    });

    socket.on('responseVerifyRoom', (value) => {
      // console.log(value);
      if(!value.admValidation) {
        // alert('Muitas pessoas na sala!');
        let room = document.getElementById(value.room);

        room.classList.add('ausente');

        room.classList.remove('livre');
        return false;
      } else if(!value.response) {
        // alert('Muitas pessoas na sala!');
        let room = document.getElementById(value.room);

        room.classList.add('ocupado');

        room.classList.remove('livre');
        return false;
      }

      $.post('room.php', {dados: value.room}, (resposta) => {
        $('body').html(resposta);
      });
    });

    
  </script>
</html>