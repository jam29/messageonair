<?php
require_once('config.php');

/*
setcookie("role","diffuseur1");

if(!isset($_COOKIE["session_sms"])) {
    setcookie("session_sms", $_GET['session_sms']) ;   
    $session = $_GET['session_sms'];                 
} else {
  $session = $_COOKIE['session_sms'];
}
*/

?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <title>MESSAGEONAIR 2.1 - Diffusion</title>
    <link rel="stylesheet" href="css/diffusion.css">
    <script src="js/jquery-1.9.1.js"></script>
   <script src="<?php echo NODEJS_ROOT_URL; ?>socket.io/socket.io.js"></script>
    <script>
    var _ppt = 0 ;
    $(document).ready(function() {
    
    var socket = io.connect("<?php echo WEB_SERVER_URL; ?>",{ path:'<?php echo NODEJS_URL_PATH; ?>socket.io' })

    socket.on('connect', function() { 
        socket.emit('diffusion:connecte',{ salle:<?php echo $_GET['salle'] ?>, id_session:<?php echo $_GET['session_sms']; ?> }) 
        socket.on("messageonair",function(data) {
          if (data.session_id == <?php echo $_GET['session_sms']; ?>) {
        	  var aff = (data.message).replace(/[\n]/gi, "<br/>" )
                  $("#question").html(aff)
            }
          })   
    })
  })
    </script>
 </head> 
 <body>
   <div id="bandeau">
     <table id="cadre">
       <tr>
         <td>
            <span id="question"></span>
         </td>
       </tr>
     </table>
   </div>
 </body>
</html>
