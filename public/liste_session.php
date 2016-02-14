<?php
require_once('config.php');
setcookie("salle", $_GET['salle'], time()+60*60*24*30)    ;
?>
<!DOCTYPE html>
<!--[if IE 8]>	 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" > <!--<![endif]-->

<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width">
 <meta name="apple-mobile-web-app-capable" content="yes">
 <title>MESSAGEONAIR 2.1 - liste des sessions</title>

 <link rel="stylesheet" href="css/bootstrap.min.css">
 <link rel="stylesheet" href="css/bootstrap-theme.min.css">
 <link rel="stylesheet" href="css/liste_session.css">
</head>

<body>
  <div class="control1"></div>
  <div class="control2"> <div class="back">&#x23EA;</div></div>
  <div class="container">
  </div>  

  <script src="js/jquery-1.9.1.js"></script>
  <script src="js/lodash.min.js"></script>
  <!--script src="js/jquery-ui-1.10.3.custom.min.js"></script-->
  <!--script src="js/jquery.ui.touch-punch.js"></script-->
  <script src="js/jquery.jeditable.mini.js"></script>
  <script src="js/moment-with-langs.min.js"></script>
  <script src="<?php echo NODEJS_ROOT_URL; ?>socket.io/socket.io.js"></script>


  <script type="text/javascript">

  var socket = io.connect("<?php echo WEB_SERVER_URL; ?>",{ path:'<?php echo NODEJS_URL_PATH; ?>socket.io' });

  socket.on('connect', function() { 
    socket.emit('gestion:sessions',{id_salle:<?php echo $_GET['salle'] ?> })
  })

  socket.on('sessions:all',function(sessions) {
    //console.log(sessions.sessions[0])
    $(".control1").html(sessions.sessions[0].nom)
    var template_session = _.template($("#tmpl_sessions").html(),sessions)
    $(".container").html(template_session)
})


 $(".control2").on('click',function() { location.href="install.php" })
 $(document).on('click','button.btn.gom1', function() { location.href="moderateur1.php?session_sms=" + this.id } )
 $(document).on('click','button.btn.gom2', function() { location.href="moderateur2.php?session_sms=" + this.id } )
 $(document).on('click','button.btn.god',  function()  { 
  //location.href="diffusion.php?salle=" + <?php echo $_GET['salle'] ; ?>+"&session_sms="+this.id 
  } )
 $(document).on('click','button.btn.goa',  function()  { 
  //location.href="animateur.php?salle=" + <?php echo $_GET['salle'] ; ?>+"&session_sms="+this.id 
 })

 $(document).on('click','button.btn.ajouter',function(){ 
  var code = prompt("Code session","code")
  if (code != null) {
    socket.emit("ajouter:session",{code:code,salle:<?php echo $_GET['salle'] ?>}) 
  }
 } )

  </script>

  <script type="text/template" id="tmpl_sessions">  
  <% for (var i = 0 ; i < sessions.length; i++ ) {  %>
   <% var datef = moment(sessions[i].date).lang('fr').format('YYYY/MM/DD') %>
   <% var tsf_debut = moment(sessions[i].date_heure_debut_sms).format('MM/DD HH:mm') %>
   <% var tsf_fin = moment(sessions[i].date_heure_fin_sms).format('MM/DD HH:mm') %>
  
   <div class="row ls" cle="<%= sessions[i].id %>">
   <div class="col-sm-1 code_session_sms"><%= sessions[i].code_session_sms %></div> 
   <div class="col-sm-4 titre_session"><%= sessions[i].titre_session_sms1 %></div> 
   <div class="col-sm-1 ts_debut_sms"><%= tsf_debut %></div>
   <div class="col-sm-1 ts_fin_sms"><%= tsf_fin %></div>
   <div class="col-sm-5">
      <button class="btn btn-primary btn-xm gom1" id="<%= sessions[i].id %>">modérateur</button>
      <button class="btn btn-primary btn-xm gom2" id="<%= sessions[i].id %>">responsab</button> 
      <button class="btn btn-danger btn-xm god"   id="<%= sessions[i].id %>">diffusion</button>
      <button class="btn btn-danger btn-xm goa"   id="<%= sessions[i].id %>">animation</button>
   </div>
   </div>
   <% } %> 

   </body>
   </html>
