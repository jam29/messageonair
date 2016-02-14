<?php
require_once('config.php');
?>
<!DOCTYPE html>
<!--[if IE 8]>	 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" > <!--<![endif]-->

<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width">
 <meta name="apple-mobile-web-app-capable" content="yes">
 <title>MESSAGEONAIR 2.1 - formulaire</title>

 <link rel="stylesheet" href="css/bootstrap.min.css">
 <link rel="stylesheet" href="css/bootstrap-theme.min.css">
</head>

<body>
  <div class="container">
    <div class="row">
      <div class="col-md-12 text-center">
        <form id="f_question" role="form">
        <div class="form-group">
          <label for="question" >Votre question pour la sessions: <?php echo $_GET['code_session'] ?></label>
          <textarea rows="3" class="form-control" id="question" placeholder="Veuillez saisir votre question ici puis envoyez la question à l'animateur en cliquant le bouton Envoyer. Merci."></textarea>
        </div>
        <button class="btn btn-default btn-lg envoyer">Envoyer</button>
        <!--button class="btn btn-default btn-lg effacer">Effacer</button-->
        <button class="btn btn-default btn-lg sortir">Liste des sessions</button>
        
        </form>
      </div>
  </div>  
</div>

  <script src="js/jquery-1.9.1.js"></script>
  <script src="js/lodash.min.js"></script>
  <script src="js/jquery-ui-1.10.3.custom.min.js"></script>
  <script src="js/jquery.ui.touch-punch.js"></script>
  <script src="js/jquery.jeditable.mini.js"></script>
  <script src="js/moment-with-langs.min.js"></script>
  <script src="<?php echo NODEJS_ROOT_URL; ?>socket.io/socket.io.js"></script>

  <script type="text/javascript">
  
  var socket = io.connect("<?php echo WEB_SERVER_URL; ?>",{ path:'<?php echo NODEJS_URL_PATH; ?>socket.io' });
  socket.on('connect', function() { 
    socket.emit('question')
  })
  

$(document).ready(function() {
  
  $(".sortir").click(function(e) { 
     e.preventDefault();
    location.href="liste_session_now.php" } )
  

  $(".effacer").click(function(e) { 
     e.preventDefault();
    $("#question").val('') } 
    )

  $(".envoyer").click(function(e)  {
         e.preventDefault();
          var question = $("#question").val();
          if ( question.length > 0 ) {
                socket.emit('formulaire:question',
                            { id_session:<?php echo $_GET['id_session'] ?> , question:question }, 
                              function(todo) { if (todo === 'efface') { console.log(todo) ; $("textarea").val('') } })
              }
            })
              
})
  </script>
  </body>
  </html>