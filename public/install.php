<!DOCTYPE html>
<!--[if IE 8]>	 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" > <!--<![endif]-->

<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width">
 <meta name="apple-mobile-web-app-capable" content="yes">
 <title>MESSAGEONAIR 2.1 - Configuration</title>

 <link rel="stylesheet" href="css/bootstrap.min.css">
 <link rel="stylesheet" href="css/bootstrap-theme.min.css">
 <link rel="stylesheet" href="css/liste_session.css">


</head>

<body>


  <div class="container">
    <div class="row">
     <div class="col-md-12 text-center">
      <div class="well text-center"><h3>Bonjour et bienvenue </h3>   </div>
    </div>
  </div>
    <div class="row">
      <div class="col-md-12 text-center">
        <button class="btn btn-lg btn-success continuer">Aller dans la session en cours <span class="badge">5</span></button> 
      </div>
    </div>

    <div class="row">
      <div class="col-md-12 text-center">
        <button class="btn btn-lg btn-danger param">Changer de configuration</button>
      </div>
    </div>
  </div>  

  <script src="js/jquery-1.9.1.js"></script>
  <script src="js/jquery.cookie.js"></script>
  <script src="js/lodash.min.js"></script>
  <script src="js/jquery-ui-1.10.3.custom.min.js"></script>
  <script src="js/jquery.ui.touch-punch.js"></script>
  <script src="js/jquery.jeditable.mini.js"></script>
  <script src="js/moment-with-langs.min.js"></script>
  <!--script src="/nodejs/141024_AMEC/socket.io/socket.io.js"></script-->


  <script type="text/javascript">

$(document).ready(function() {
  var duree = 5 
  setInterval(function() { 
    if (duree==-1) { 
      location.href="route.php" 
    } else {
      $(".badge").html(duree--) }
    }, 1000)

  $(document).on('click','button.btn.continuer',function(){ location.href="route.php" } )
  $(document).on('click','button.btn.param',function(){ 
    $.removeCookie('salle')
    $.removeCookie('role')
    $.removeCookie('session_sms')
    location.href="gestion_salle.php" } )
})
</script>
</body>
</html>
