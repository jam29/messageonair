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
 <title>MESSAGEONAIR 2.1</title>
 
 <link rel="stylesheet" href="css/moderateur.css">

 <script src="js/custom.modernizr.js"></script> 
 <style>
 #onair {
  position:absolute ; 
  top:100px;
  left:20px;
  width:980px ;
  height:400px ;
  font-size:38px;
  border: 1px solid #FE0000;
  overflow: auto;
z-index:9999;
}

#next {
  position:absolute ; 
  top:510px;
  left:20px;
  width:980px ;
  height:210px ;
  font-size:28px;
  border: 1px solid #936FAC;
  overflow: auto;
  z-index:9999;
}
</style>
</head>

<body>

  <div class="global">
    <div id="onair"></div>
    <div id="next"></div>
  </div>

  <div class="container"> </div>     

</div> 

<script src="js/jquery-1.9.1.js"></script>
<script src="js/lodash.min.js"></script>
<script src="js/jquery-ui-1.10.3.custom.min.js"></script>
<script src="js/jquery.ui.touch-punch.js"></script>
<script src="<?php echo NODEJS_ROOT_URL; ?>socket.io/socket.io.js"></script>

<script type="text/javascript">

$(document).ready(function() {
  
  var socket = io.connect("<?php echo WEB_SERVER_URL; ?>",{ path:'<?php echo NODEJS_URL_PATH; ?>socket.io' });

  socket.on('connect', function() { 
    socket.emit('animateur:connecte',{ salle:<?php echo $_GET['salle'] ?>, id_session:<?php echo $_GET['session_sms']; ?> });
    socket.on("messageonair",function(data) {
      var onair="";
      if (data.id_session == <?php echo $_GET['session_sms']; ?>) {
        if(data.message) {
          onair = (data.message).replace(/[\n]/gi, "<br/>" );
        }
        $("#onair").html(onair);
      }
    });

    socket.on("messagenextonair",function(data) {
      var next="";
      if (data.id_session == <?php echo $_GET['session_sms']; ?>) {
        if(data.message) {
          next = (data.message || {} ).replace(/[\n]/gi, "<br/>" );
        }
        $("#next").html(next);
      }
    });
  });
}); // fin document.ready()...
</script>
</body>
</html>