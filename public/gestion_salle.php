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
 <title>MESSAGEONAIR 2.1 - Gestion salles</title>

 <link rel="stylesheet" href="css/bootstrap.min.css">
 <link rel="stylesheet" href="css/bootstrap-theme.min.css">
 <link rel="stylesheet" href="css/liste_session.css">

</head>

<body>

 <div class="control1"></div>
  <div class="control2"> <div class="back">&#x23EA;</div></div>


  <!--div class="control7">
     <button class="btn btn-success ajouter">AJOUTER UNE SALLE</button>
  </div-->

  <div class="container">
  </div>  

  <script src="js/jquery-1.9.1.js"></script>
  <script src="js/lodash.min.js"></script>
  <!--script src="js/jquery-ui-1.10.3.custom.min.js"></script>
  <script src="js/jquery.ui.touch-punch.js"></script>
  <script src="js/jquery.jeditable.mini.js"></script>
  <script src="js/moment-with-langs.min.js"></script-->

  <script src="<?php echo NODEJS_ROOT_URL; ?>socket.io/socket.io.js"></script>

  <script type="text/javascript">

  var socket = io.connect("<?php echo WEB_SERVER_URL; ?>",{ path:'<?php echo NODEJS_URL_PATH; ?>socket.io' });

  socket.on('connect', function() { 
    socket.emit('gestion:salles')
  })

  socket.on('salles:all',function(salles) {
    var template_salle = _.template($("#tmpl_salle").html(),salles)
    $(".container").html(template_salle)


/*
  uniquement en application seule
  
  $(".nom").editable(function(value,settings) {
    
	var lid = $(this).parent().attr('cle')
      socket.emit("change:nom_salle",{id:lid,value:value }) 
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
      socket.emit("delete:salle", { id:lid })
    }
  })
*/
})


 $(".control2").on('click',function() { location.href="install.php" })
  $(document).on('click','button.btn.go',function(){ location.href="liste_session.php?salle=" + this.id } )
  $(document).on('click','button.btn.ajouter',function(){ 
  var salle = prompt("Salle","")
  if (salle != null) {
    socket.emit("ajouter:salle",{salle:salle}) 
  }
 } )

  </script>

  <script type="text/template" id="tmpl_salle">  
  <% for (var i = 0 ; i < salles.length; i++ ) {  %>
   <div class="row" cle="<%= salles[i].id %>">
   <div class="col-sm-2"><button class="btn btn-primary go" style="width:200px" id="<%= salles[i].id %>"><%= salles[i].nom %></button></div>
   <!-- div class="col-sm-10 nom"><%= salles[i].nom %></div-->
   <!-- div class="col-md-1 delete"><button class="btn btn-danger">DELETE</button></div-->
   </div>
   <% } %> 

   </body>
   </html>
