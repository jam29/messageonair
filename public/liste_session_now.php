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
 <title>MESSAGEONAIR 2.1 - Liste des sessions actuelles pour toutes les salles</title>
 <link rel="stylesheet" href="css/bootstrap.min.css">
 <link rel="stylesheet" href="css/bootstrap-theme.min.css">
 <link rel="stylesheet" href="css/liste_session.css">
</head>

<body>
 
  <div class="container">
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
    socket.emit('gestion:sessions_now')
  })

  socket.on('sessions:now',function(sessions) {
    var template_session = _.template($("#tmpl_sessions").html(),sessions)
    $(".container").html(template_session)
  })
 
 $(document).on('click','button.btn.go_formulaire',function() { location.href="formulaire.php?id_session=" + this.id + "&code_session="+$(this).attr('code') } )

  </script>

  <script type="text/template" id="tmpl_sessions">  
  <% for (var i = 0 ; i < sessions.length; i++ ) {  %>
   <% var datef = moment(sessions[i].date).lang('fr').format('YYYY/MM/DD') %>
   <% var tsf_debut = moment(sessions[i].date_heure_debut_sms).format('YYYY/MM/DD HH:mm') %>
   <% var tsf_fin = moment(sessions[i].date_heure_fin_sms).format('YYYY/MM/DD HH:mm') %>
   <div class="row" cle="<%= sessions[i].id %>">
   <div class="col-xs-2 salle" ><button class="btn btn-primary go_formulaire" id="<%= sessions[i].id %>" code="<%= sessions[i].code_session_sms %>"><%= sessions[i].nom %></button></div>
   <div class="col-xs-2 code_session"><%= sessions[i].code_session_sms %></div> 
   <div class="col-xs-4 titre_session"><%= sessions[i].titre_session_sms1 %></div> 
   <div class="col-xs-2 ts_debut_sms"><%= tsf_debut %></div>
   <div class="col-xs-2 ts_fin_sms"><%= tsf_fin %></div>
   </div>
   <% } %> 

   </body>
   </html>
