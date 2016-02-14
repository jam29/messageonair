<?php
 require_once('config.php');
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
 <script src="js/moment-with-langs.min.js"></script> 
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
        <p class="non_lus">&nbsp;</p>
  </div>

  <div class="control6">
    <div class="back">&#x23EA;</div>
         <img id="onair" src="img/next.png" />
  </div>

  <div class="control8"></div>
  <div class="control9"></div>
  <div class="control10"></div>
  <div class="control11"></div>

<div id="global_onair">
  <div class="controlstop status_onair">&nbsp;</div>
  <div id="messageonair"></div>
</div>

  <div class="container"> </div>
      
  <div class="footer"> </div>      

</div> 
		
<script src="js/jquery-1.9.1.js"></script>
<script src="js/lodash.min.js"></script>
<script src="js/jquery-ui-1.10.3.custom.min.js"></script>
<script src="js/jquery.ui.touch-punch.js"></script>
<script src="<?php echo NODEJS_ROOT_URL; ?>socket.io/socket.io.js"></script>
   
<script type="text/javascript">

 $(document).ready(function() {
  var DIFFUSE = 1
  var MESSAGES_LUS = 0 ;
  var socket = io.connect("<?php echo WEB_SERVER_URL; ?>",{ path:'<?php echo NODEJS_URL_PATH; ?>socket.io' });


  socket.on('connect', function(){ 
     socket.emit('get:session', {id:<?php echo $session ;?>} ,function(res) {
        var salle = res.id_salle ;
        $(".control8").html( res.nom + "-" + res.code_session_sms+":"+res.titre_session_sms1 )
        $(".control9").html( "début réception sms: "+moment(res.date_heure_debut_sms).format('HH:mm') )
        $(".control10").html("fin réception sms: "+moment(res.date_heure_fin_sms).format('HH:mm') )
        $(".control11").html(res.no_reception)
    });

     socket.emit('moderateur2:connecte',{id_session:<?php echo $session ?>}) 
     socket.emit('sms:all2',{ id:<?php echo $session  ?> })  
      $(".back").on("click",function() { window.location.href="install.php" } )
  })

  socket.on('legende:get',function(legende) {
    var template_legende = _.template($("#tmpl_legende").html(),legende)
    $(".footer").html(template_legende)
  })

  socket.on("messageonair",function(data){ 
     if (data.session_id == <?php echo $session ?>) {
    var aff="" ;
	  if (data.message) {
        aff = (data.message).replace(/[\n]/gi, "<br/>" )
            $("#messageonair").html(aff)
        }
        else {
            $("#messageonair").html("")
        }
    }   
  })  

  socket.on('sms:off',function(data){
    if (data.id_session == <?php echo $session ?>) { 
      $(".controlstop").addClass('status_offair')
    }
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
                  socket.emit('sms:messagenextonair' , {id_session:<?php echo $session  ?> , message:messnext ,idnext:idnext , messagesLus:MESSAGES_LUS } )
                  socket.emit('change:ordre',{ sequence:sorted } )
                }        
            });
   })
   
// reception changement de couleur pour 1 message
socket.on('change:color',function(data) {
   $("li#li-"+data.id).removeClass(data.oldcolor).addClass(data.newcolor).attr('color',data.newcolor)
})

socket.on('change:priorite',function(data){
  if ((data.id_session == <?php echo $session ?> ) &&  (data.priorite == 2) ) { $(".control4").show() }
})

$(".controlstop").click(function(){
  $(this).toggleClass('status_offair');
  if ($(this).hasClass('status_offair')) {
    socket.emit('close:sms',{ session_id:<?php echo $session  ?> } ) 
    DIFFUSE = 0
  } else {
    var message_onair = $("#messageonair").html()
    socket.emit('open:sms',{ session_id:<?php echo $session  ?>,message:message_onair } )
    DIFFUSE = 1
  }
})


$('#refresh').click(function() { 
  $(".control4").hide()
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
   
   
  MESSAGES_LUS = 0
  $("#onair").on('click',function() {

    // le message en cours (onaire) est récupéré à partir de la ligne 1 de la liste (message + id) 
      var messonair = $("li.ui-state-default span.message").first().html()
      var lid = $("li.ui-state-default span.message:visible").first().parent().attr('id')

    // et le message suivant est récupéré à partir de la ligne 2 de la liste (message + id) 
      var messnext = $("li.ui-state-default:nth-child(2) span.message").html()
      var idnext = $("li.ui-state-default:nth-child(2):visible").attr('id')

      // console.log(messnext+":"+idnext)
      // idnext est l'id du suivant
      
      if (idnext && idnext.length > 0 ) {
            idnext = idnext.substring(3)
      } else {
            idnext=0 
            // $("#messageonair").html("")
      }
        
      // lid est l'id du message en diffusion
       if (lid && lid.length > 0 ) {
              lid = lid.substring(3) 
       } else { 
           lid=0 
       }       
      
      var aff = messonair.replace(/[\n]/gi, "<br/>" )

      $("#messageonair").html(aff) 
      $("li.ui-state-default").first().hide()
      
     // alert('stop')
     
     if (MESSAGES_LUS == 1 ) {
      MESSAGES_LUS=0
     $(".control7>p").removeClass("lus")
      socket.emit('sms:onairlus', { session_id:<?php echo $session ?> , message:messonair, id:lid },function(){
         if (DIFFUSE == 1) {
          socket.emit('open:sms',{ session_id:<?php echo $session  ?>,message:messonair } ) 
         }     
      })

     } else {
      socket.emit('sms:onair', { session_id:<?php echo $session ?> , message:messonair, id:lid, next:idnext ,messnext:messnext },function(){
         if (DIFFUSE == 1) {
         socket.emit('open:sms',{ session_id:<?php echo $session  ?>,message:messonair } ) 
         }     
      })
      // pour l'animateur message suivant
       socket.emit('sms:messagenextonair', { id_session:<?php echo $session ?> , message:messnext });      
      } // if message_lus  
   
   })

   $(".cherche").on('input',function(){
      var mot = $(this).val()
      $("li span.message:not(:contains("+mot+"))").parent().hide()
      $("li span.message:contains("+mot+")").parent().show()
   })
   
   
    $(".control7>p").on("click",function() {
        $(this).toggleClass('lus')
        if ($(this).hasClass('lus')) {
            MESSAGES_LUS = 1
            socket.emit('sms:lus',{ id:<?php echo $session  ?> }) 
        } else {
            MESSAGES_LUS = 0
            $(".filtre_cat").removeClass('enfonce');
            socket.emit('sms:all2',{ id:<?php echo $session  ?> })   
        }
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
