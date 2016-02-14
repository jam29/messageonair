<?php


require_once('config.php');
$serveur_port = SERVER.':'.PORT_NODE;

setcookie("role","moderateur2");

if(!isset($_COOKIE["session_sms"])) {
  setcookie("session_sms", $_GET['session_sms']) ;   
  $session = $_GET['session_sms'];
} else {
  $session = $_COOKIE['session_sms'];
}

?>
<!DOCTYPE html>
<!--[if IE 8]>	 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" > <!--<![endif]-->

<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width">
 <meta name="apple-mobile-web-app-capable" content="yes">
 <title>MESSAGEONAIR 2.1 - Responsable de séance</title>
  
 <link rel="stylesheet" href="css/moderateur.css">
 <link rel="stylesheet" href="css/moderateur2.css">
 <link href="css/jquery-ui-1.10.3.custom.css" rel="stylesheet">
   
 <script src="js/custom.modernizr.js"></script> 
</head>

<body>

  <div class="global">
     <div class="control1">
         <div class="filtre_cat" id="multicolor"> </div>
         <div class="filtre_cat" id="bleu"> </div>
         <div class="filtre_cat" id="jaune">  </div>
         <div class="filtre_cat" id="rose">  </div>
         <div class="filtre_cat" id="vert">  </div>
         <div class="filtre_cat" id="violet">  </div>
         <div class="filtre_cat" id="blanc"> </div>
  </div>

  <div class="control4">   
         <img id="refresh" src="img/refresh.png">
  </div>

  <div class="control7">
        <p id="lus"><img src="img/review.png"></p>
  </div>

  <div class="control6">
         <img id="onair" src="img/next.png" />
  </div>


<div class="controlstop"><img src="img/stop.png"></div>

  <div id="messageonair"></div>

  <div class="container"> </div>
      
  <div class="footer"> </div>      

</div> 
		
<script src="js/jquery-1.9.1.js"></script>
<script src="js/lodash.min.js"></script>
<script src="js/jquery-ui-1.10.3.custom.min.js"></script>
<script src="js/jquery.ui.touch-punch.js"></script>
<script src="http://<?php echo $serveur_port; ?>/socket.io/socket.io.js"></script>
   
<script type="text/javascript">

 $(document).ready(function() {
   
  var socket = io.connect("http://<?php echo $serveur_port; ?>");


  socket.on('connect', function(){ socket.emit('moderateur2:connecte',{id_session:<?php echo $session ?>}) 
     socket.emit('sms:all2',{ id:<?php echo $session  ?> })  
  })

  socket.on('legende:get',function(legende) {
    var template_legende = _.template($("#tmpl_legende").html(),legende)
    $(".footer").html(template_legende)
  })

  socket.on("messageonair",function(data){ 
        var aff="" 
	if (data.message) {
          var aff = (data.message).replace(/[\n]/gi, "<br/>" )
        }
        $("#messageonair").html(aff)
  })   

  socket.on('sms:all',function(data) {

            var template = _.template($("#tmpl_messages").html(),data)
            $(".container").html(template)
      
            $("#sortable").sortable(
              {
                
                stop:function(event,ui){
                  var sorted = $(this).sortable("serialize")
                  var letri = sorted.split('&')
                  var idnext =  letri[0].substring(5) 
                  var messnext = $("li#li-"+idnext+" span.message").html()
                  socket.emit('sms:messagenextonair' , {id:<?php echo $session  ?> , message:messnext  } )
                  socket.emit('change:ordre',{ sequence:sorted } )
                }
            
            
            });
           
   })
   
 // reception changement de couleur pour 1 message
 socket.on('change:color',function(data){
 console.log(data)
   $("li#li-"+data.id).removeClass(data.oldcolor).addClass(data.newcolor).attr('color',data.newcolor)
 })

$(".controlstop").click(function(){socket.emit('close:sms',{ session_id:<?php echo $session  ?> } ) })

   $('#refresh').click(function() { 
    $(".filtre_cat").removeClass('enfonce');
     socket.emit('sms:all2',{ id:<?php echo $session  ?> })  
   });

   $(".filtre_cat").on('click',function(){
    var categorie = $(this).attr('id')
    if (categorie == 'multicolor') {
        $(".filtre_cat").removeClass('enfonce')
        $("li").show()
    } else {
        $(this).toggleClass('enfonce'); 
        $("."+categorie).toggle();
    }
   })
   
   $("#onair").on('click',function() {
      var messonair = $("li.ui-state-default span.message").first().html()
      var lid = $("li.ui-state-default span.message:visible").first().parent().attr('id')
      
      var messnext = $("li.ui-state-default:nth-child(2) span.message").html()
      var idnext = $("li.ui-state-default:nth-child(2):visible").attr('id')
      
      // alert(idnext+"|"+messnext)
      
      if (idnext && idnext.length > 0 ) {
            idnext = idnext.substring(3)
      } else {
            idnext="";
      }
      
       if (lid && lid.length > 0 ) {
              lid = lid.substring(3) 
       } else { 
           lid="";
       }       
      
      var aff = messonair.replace(/[\n]/gi, "<br/>" )

      $("#messageonair").html(aff) 
      $("li.ui-state-default").first().hide()
      
      socket.emit('sms:onair', { session_id:<?php echo $session ?> , message:messonair, id:lid, next:idnext ,messnext:messnext } )  
   })

   $(".cherche").on('input',function(){
      var mot = $(this).val()
      $("li span.message:not(:contains("+mot+"))").parent().hide()
      $("li span.message:contains("+mot+")").parent().show()
   })
   
   
  $("#lus").on("click",function  () {
        socket.emit('sms:lus',{ id:<?php echo $session  ?> })  
  })


 }); // fin document.ready()...

</script>
</body>
</html>

<script type="text/template" id="tmpl_messages">  
 <ul id="sortable">

  <% for (var i = 0 ; i < data.donnee.length; i++ ) {  %>   
    <li class="ui-state-default <%= data.donnee[i].categorie %> <% if(data.donnee[i].flag == 3) { print("voile") }  %>" color="<%= data.donnee[i].categorie %>" id="li-<%= data.donnee[i].id %>">
      <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
      <span class="id"><%= data.donnee[i].id %></span>
      <span class="message_origine"><%= data.donnee[i].message_origine %></span>
    
      <% var mae = data.donnee[i].message_a_editer ; mae = mae.replace(/(\r\n|\n|\r)/gm,"<br>") %>
      <span class="message" ><%= mae %></span>
    </li>
  <% } %>
 </ul>                             
</script>

<script type="text/template" id="tmpl_legende">  
    <% for (var i = 0 ; i < legende.length; i++ ) {  %>   
      <div class="fil" id="f_<%= legende[i].id_couleur %>"> </div>    <div class="til" id="lib_<%= legende[i].id_couleur %>" ><%= legende[i].legende %></div>
    <% } %>   
</script>
