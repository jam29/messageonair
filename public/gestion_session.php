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
 <title>MESSAGEONAIR 2.1 - Gestion sessions</title>

 <link rel="stylesheet" href="css/bootstrap.min.css">
 <link rel="stylesheet" href="css/bootstrap-theme.min.css">
 <link rel="stylesheet" href="css/liste_session.css">


</head>

<body>
  <div class="control7">
     <button class="btn btn-success ajouter">AJOUTER UNE SESSION</button>
  </div>

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
    socket.emit('gestion:sessions',{id_salle:<?php echo $_GET['salle'] ?> })
  })

  socket.on('sessions:all',function(sessions) {
    var template_session = _.template($("#tmpl_sessions").html(),sessions)
    $(".container").html(template_session)

    $(".titre_session").editable(function(value,settings) {
    
    	var lid = $(this).parent().attr('cle')
      socket.emit("change:titre_session",{id:lid,value:value }) 
       return(value) } , {
     event: 'click',
     type:'text',
     submit:'ok',
     cancel:'annul',
     tooltip: 'Clicker pour éditer'
  })


 $(".ts_debut_sms").editable(function(value,settings) {
  var lid = $(this).parent().attr('cle') 
  socket.emit("change:ts_debut_sms",{id:lid,value:value }) 
   return(value) } , {
     event: 'click',
     type:'text',
     submit:'ok',
     cancel:'annul',
     tooltip: 'Clicker pour éditer'
  })

$(".ts_fin_sms").editable(function(value,settings) {
  var lid = $(this).parent().attr('cle') 
  socket.emit("change:ts_fin_sms",{id:lid,value:value }) 
   return(value) } , {
     event: 'click',
     type:'text',
     submit:'ok',
     cancel:'annul',
     tooltip: 'Clicker pour éditer'
  })


  $(".delete").on('click',function(){
    if (confirm('confirmez vous la suppression')){
      var lid = $(this).parent().attr('cle') 
      socket.emit("delete:session", { id:lid ,id_salle:<?php echo $_GET['salle'] ?> })
    }
  })

})

  //$("button").bind('click',function(){ location.href="moderateur1.php?session_sms=" + this.id })
  $(document).on('click','button.btn.go',function(){ location.href="moderateur1.php?session_sms=" + this.id } )
 $(document).on('click','button.btn.ajouter',function(){ 
  var code = prompt("Code session","code")
  if (code != null) {
    socket.emit("ajouter:session",{code:code,id_salle:<?php echo $_GET['salle'] ?>}) 
  }
 } )

  </script>

  <script type="text/template" id="tmpl_sessions">  
  <% for (var i = 0 ; i < sessions.length; i++ ) {  %>
  
   <% var tsf_debut = moment(sessions[i].date_heure_debut_sms).format('YYYY/MM/DD HH:mm') %>
      <% var tsf_fin = moment(sessions[i].date_heure_fin_sms).format('YYYY/MM/DD HH:mm') %>
   <div class="row" cle="<%= sessions[i].id %>">
   <div class="col-md-1"><button class="btn btn-primary go" id="<%= sessions[i].id %>"><%= sessions[i].id %></button></div> 
   <div class="col-md-1 code_session"><%= sessions[i].code_session_sms %></div> 
   <div class="col-md-2 titre_session"><%= sessions[i].titre_session_sms1 %></div> 
    
   <div class="col-md-2 ts_debut_sms"><%= tsf_debut %></div>
   <div class="col-md-2 ts_fin_sms"><%= tsf_fin %></div>
   <div class="col-md-1 delete"><button class="btn btn-danger">X</button></div>
   </div>
   <% } %> 

   </body>
   </html>

