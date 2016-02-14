<?php
require_once('config.php');

setcookie("role","moderateur1",time()+60*60*24*30);

if(!isset($_COOKIE["session_sms"])) {
    setcookie("session_sms", $_GET['session_sms'],time()+60*60*24*30) ;   
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
 <title>MESSAGEONAIR 2.0 - Modérateur</title>

 <link rel="stylesheet" href="css/moderateur.css">
 <link href="css/jquery-ui-1.10.3.custom.css" rel="stylesheet">
 
 <script src="js/custom.modernizr.js"></script> 
</head>

<body>

  <div class="global">

    <div class="control0">
      &nbsp;
    </div>

    <div class="control1">
     <div class="filtre_cat" id="multicolor"> </div>
     <div class="filtre_cat" id="bleu"> </div>
     <div class="filtre_cat" id="jaune"> </div>
     <div class="filtre_cat" id="rose"> </div>
     <div class="filtre_cat" id="vert"> </div>
     <div class="filtre_cat" id="violet"> </div>
     <div class="filtre_cat" id="blanc"> </div>
   </div>

   <div class="control2">
    <div class="back">&#x23EA;</div>
     <div class="filtre_pr" id="pr2" pr="2"> </div>
     <div class="filtre_pr" id="pr1" pr="1"> </div>
     <div class="filtre_pr" id="pr0" pr="0"> </div>
   </div>

   <div class="control4">   
     <img id="refresh" src="img/refresh.png">
   </div>

   <div class="control5">
     <span id="compteur">0</span>
   </div>

   <div class="control6">
    <form>
      <input type="text" class="cherche"/>
    </form>
  </div>  

  <div class="control7"></div>
  <div class="control8"></div>
  <div class="control9"></div>
  <div class="control10"></div>
  <div class="control11"></div>
  <div class="container"></div>

  <div class="footer"> 
    <div class="fil" id="f_bleu">   </div>   <div class="til" id="lib_bleu">bleu</div>
    <div class="fil" id="f_rose">   </div>   <div class="til" id="lib_rose">rose</div>
    <div class="fil" id="f_jaune">  </div>   <div class="til" id="lib_jaune">jaune</div>
    <div class="fil" id="f_vert">   </div>   <div class="til" id="lib_vert">vert</div>
    <div class="fil" id="f_violet"> </div>   <div class="til" id="lib_violet">violet</div>
    <div class="fil" id="f_blanc">  </div>   <div class="til" id="lib_blanc">blanc</div>
  </div>      

  <div id="modif-message">
    <div id="close">close</div>
    <form>
      <fieldset>
        <label for="origin">Origine</label><br/>
        <textarea readonly id="origin" rows=5 cols=40 class="text ui-widget-content ui-corner-all" /></textarea>
        <br/>
        <label for="real">Editer</label><br/>
        <textarea id="real" rows=5 cols=40 class="text ui-widget-content ui-corner-all" /></textarea>
      </fieldset>
    </form>
  </div>

  <div id="continu">
    <p>Voulez vous continuer d'accepter les questions pour cette session ?</p>
    <form>
       <button class="moaButton" id="continu_non"><b>NON</b></button>
       <button class="moaButton" id="continu_oui"><b>OUI</b></button>
    </form>
    <p id="continu_offset"></p>

</div> 

<script src="js/jquery-1.9.1.js"></script>
<script src="js/jquery.cookie.js"></script>
<script src="js/lodash.min.js"></script>
<script src="js/jquery-ui-1.10.3.custom.min.js"></script>
<script src="js/jquery.ui.touch-punch.js"></script>
<script src="js/jquery.jeditable.mini.js"></script>
<script src="js/moment-with-langs.min.js"></script>

<script src="<?php echo NODEJS_ROOT_URL; ?>socket.io/socket.io.js"></script>

<script type="text/javascript">

$(document).ready(function() {

  var socket = io.connect("<?php echo WEB_SERVER_URL; ?>",{ path:'<?php echo NODEJS_URL_PATH; ?>socket.io' });

socket.on('disconnect', function(){
   $(".control7").html('<img src=img/no-wifi.png>')
}); 
  
socket.on('reconnect', function() {
    $(".control7").html('reconnection')
}); 


  socket.on('connect', function() { 
    $(".control7").html('<img src=img/wifi.png>');
    socket.emit('moderateur1:connecte',{id_session:<?php echo $session ;?>})
    socket.emit('get:session', {id:<?php echo $session ;?>} ,function(res) {
        var salle = res.id_salle ;
        $(".back").on("click",function() { window.location.href="install.php" } )
        $(".control8").html( res.nom + "-" + res.code_session_sms+":"+res.titre_session_sms1 )
        $(".control9").html( "début réception sms: "+moment(res.date_heure_debut_sms).format('HH:mm') )
        $(".control10").html("fin réception sms: "+moment(res.date_heure_fin_sms).format('HH:mm') )
        $(".control11").html(res.no_reception)
    })

    socket.emit('sms:all',{ id:<?php echo $session ;?> })  
    $("#compteur").text('0') ; $("#refresh").fadeOut('slow') ;
  })

  var verif = setInterval(
    function() {

      socket.emit('get:session', {id:<?php echo $session ;?>} ,function(res) {
         var now = moment().format()
         var fin = moment(res.date_heure_fin_sms).format()
         var nfin = moment(res.date_heure_fin_sms).format('HH:mm')
         $(".control10").html("fin réception sms: "+nfin)
        // console.log(now+"---"+fin)

         if (now > fin) { 
            
            $("#continu").show()
            var duree = 10
            
            /*
            var decompte = setInterval(function(){ 
            $("#continu_offset").html(duree)  
            if (duree-- == 0 ) { $("#continu").hide()
             socket.emit('change:ts_fin',{ id:res.id , fin:fin })  
             clearInterval(decompte);  }  
            }
            ,1000)
            
            */

            $(".moaButton").click(function(e) {

                  e.preventDefault()
                  //clearInterval(decompte)
                  $("#continu").hide() 
                  if($(this).attr('id') == "continu_non") 
                    {  location.href="liste_session.php?salle="+res.id_salle   } 
                  else 
                    { 
                      socket.emit('change:ts_fin',{ id:res.id , fin:fin },function(data) {  }) 
                    }
            })
         }
         // clearInterval(verif)
      })} 
     ,15000)
     

  socket.on('legende:get',function(legende) {
    var template_legende = _.template($("#tmpl_legende").html(),legende)
    $(".footer").html(template_legende)
    
    $(".til").editable(function(value,settings) {
     var lid = $(this).attr('id'); lid = lid.substring(4) ; 
     socket.emit( "legende:change" , { session_id:<?php echo $session ?>, id:lid , value:value } ) ;
     return (value);
     },{
     event: 'click',
     type:'text',
     submit:'ok',
     cancel:'annul',
     tooltip: 'Clicker pour éditer',
     maxlength:15
    })
  })

  socket.on('new:sms',function(id) { 
     if(id.id == <?php echo $session;?> ) {
      var compteur = parseInt($("#compteur").text(),10)
      compteur++
      $("#compteur").text(compteur.toString())
      $("#refresh").fadeIn('slow')
     }
 })

  socket.on('sms:all',function(data) {
   
   var template = _.template($("#tmpl_messages").html(),data)

   $(".container").html(template)

 var pr0 = $.cookie("pr_0") ;
 if (pr0 == "enfonce") { 
   $("div.filtre_pr#pr0").addClass('enfonce');
    $("li.pr_0").hide();
 }

   $('.message').unbind().on('click',function() {
    $("#modif-message").show()
    var lid = $(this).parent().attr('id') 
    var origine = $('li#'+lid+' div.message_origine').text() ; 
    $("textarea#origin").val(origine)
    var thereal = $('li#'+lid+' div.message_a_editer').text() ; 
    $("textarea#real").val(thereal) 

    $( "textarea#real" ).unbind().keyup(function() { 
      var message = $( this ).val(); 
      socket.emit('change:message',{id:lid,message:message})
      $("li#"+lid+" div.message_a_editer").text(message.replace(/[\n]/gi, "<br/>" ))
    })

    $("#close").on('click',function(){ 
      $("textarea#real").unbind() 
      $("#modif-message").hide()
    }) 

  })

  socket.on('messageonair',function(data) {
    if (data.session_id == <?php echo $session ?>) {
    $("div.flag").each( function(index,elt) 
         { 
          if ( $(elt).text() == ">>ENCOURS" ) 
           
            { 
              //console.log(  $(elt).text() )
              $(elt).parent().hide() 
            } 
       }
    ) 
    $(".flag").html(">>PAS DIFFUSÉ"); 
    $("li#"+data.id+" div.flag").html(">>ENCOURS") ;
  }
  })

//$('.control0').click(function(){$('.control1').toggle();$('.categorie').toggle()})
$('.control0').click(function() { window.location.href="install.php"});


// emission changement de couleur pour 1 message
$('.categorie div').on('click',function(){ 
  var lid = $(this).parent().parent().parent().attr('id')
  var oldcolor = $(this).parent().parent().parent().attr('color') 
  var newcolor = $(this).attr('class') ; newcolor = newcolor.substring(3) ; 
  socket.emit('change:color',{id:lid,newcolor:newcolor,oldcolor:oldcolor})
})

// emission changement de priorite pour 1 message
$('.priorite div').on('click',function() { 
  var lid = $(this).parent().parent().parent().attr('id') ;
  var priorite = $(this).attr('class') ; priorite = priorite.substr(5,1) 
  socket.emit('change:priorite', { id:lid, priorite:priorite, id_session: <?php echo $session ?> })
  var that = this 
  if ($("div.filtre_pr#pr"+priorite).hasClass('enfonce')) { 
   $("li#"+lid).hide() 
 } 
})
})

 // reception changement de couleur pour 1 message
 socket.on('change:color',function(data){
   $("li#"+data.id).removeClass(data.oldcolor).addClass(data.newcolor).attr('color',data.newcolor)
 })

 // reception changement de priorite pour 1 message
 socket.on('change:priorite',function(data){ 
      $("li#"+data.id+" div.tdbord div.priorite div").addClass('enfonce') ; // enfonce tous les boutons...
      $("li#"+data.id+" div.tdbord div.priorite div.li_pr"+data.priorite).removeClass('enfonce') ; // ... et "désenfonce" le bouton concerné.
      $("li#"+data.id).attr('class',
       function(i, c){
        return c.replace(/\bpr_\S+/g, 'pr_'+data.priorite);
      });
    })

 // reception d'une modification de message
 socket.on('change:message',function(data){
   $("li#"+data.id+" div.message" ).html((data.message).replace(/(\r\n|\n|\r)/gm,"<br>"))     
 })

 // recuperation des nouveaux messages (1ere fois puis à chaque fois que compteur est > 0)
 $('#refresh').click(function() {
  $(".filtre_cat").removeClass('enfonce')
  socket.emit('sms:all',{ id:"<?php echo $session ?>" })  
  $("#compteur").text('0') ; $("#refresh").fadeOut('slow') ; 
});


 $(".filtre_cat").on('click',function(){
  var categorie = $(this).attr('id')

  if (categorie == 'multicolor') {
    //$(".filtre_cat#multicolor").toggleClass('enfonce')
    if ($(".filtre_cat#multicolor").hasClass('enfonce')){
        $(".filtre_cat").removeClass('enfonce')
         $("li").show()
      } else {
        $(".filtre_cat").addClass('enfonce')
         $("li").hide()
      }
   

  } else {

    $(this).toggleClass('enfonce'); 
    $("."+categorie).toggle();

  }

  if ( $("div.filtre_pr#pr0").hasClass('enfonce')) { $("li.pr_0").hide() }
    if ( $("div.filtre_pr#pr1").hasClass('enfonce')) { $("li.pr_1").hide() }
      if ( $("div.filtre_pr#pr2").hasClass('enfonce')) { $("li.pr_2").hide() }

    })

 $(".filtre_pr").on('click',function() {
  var priorite = $(this).attr('pr') ;
  $(this).toggleClass('enfonce')
  $(".pr_"+priorite).toggle()
  if ( $("div.filtre_pr#pr0").hasClass('enfonce')) { $("li.pr_0").hide() ;  $.cookie("pr_0","enfonce");} else { $.cookie("pr_0","");}

  if ( $("div.filtre_cat#blanc").hasClass('enfonce'))  { $("li.blanc").hide()  }
    if ( $("div.filtre_cat#bleu").hasClass('enfonce'))   { $("li.bleu").hide()   }
      if ( $("div.filtre_cat#jaune").hasClass('enfonce'))  { $("li.jaune").hide()  }
        if ( $("div.filtre_cat#rose").hasClass('enfonce'))   { $("li.rose").hide()   }
          if ( $("div.filtre_cat#vert").hasClass('enfonce'))   { $("li.vert").hide()   }
            if ( $("div.filtre_cat#violet").hasClass('enfonce')) { $("li.violet").hide() }
          })
 
 $(".cherche").on('input',function(){
  var mot = $(this).val()
  $("li div.message:not(:contains("+mot+"))").parent().hide()
  $("li div.message:contains("+mot+")").parent().show()
})
 
}); // fin document.ready()...

</script>
</body>
</html>

<script type="text/template" id="tmpl_messages">  
<ul id="sortable">
<% for (var i = 0 ; i < data.donnee.length; i++ ) {  %>   
  <% if (data.donnee[i].flag == 3)  { continue  } %>
  <li class="ui-state-default <%= data.donnee[i].categorie %> pr_<%= data.donnee[i].priorite %>" color="<%= data.donnee[i].categorie %>" id="<%= data.donnee[i].id %>">
  <div class="flag"><% if (data.donnee[i].flag == 0) {%>>>PAS DIFFUSÉ<%}%><% if (data.donnee[i].flag == 1) {%>>>PAS DIFFUSÉ<%}%><% if (data.donnee[i].flag == 2) {%>>>ENCOURS<%}%></div>

  <div class="id"><%= data.donnee[i].id %></div>

  <div class="message_origine"><%= data.donnee[i].message_origine %></div>

  <div class="message_a_editer"><%= data.donnee[i].message_a_editer  %></div>

  <% var mae = data.donnee[i].message_a_editer ; mae = mae.replace(/(\r\n|\n|\r)/gm,"<br>") %>

  <div class="message" ><%= mae %></div>

  <div class="tdbord">

  <div class="priorite">
  <% if (data.donnee[i].priorite == 2) {  %> <div class="li_pr2"> </div> <% } else { %>  <div class="li_pr2 enfonce"> </div> <% } %> 
  <% if (data.donnee[i].priorite == 1) {  %> <div class="li_pr1"> </div> <% } else { %>  <div class="li_pr1 enfonce"> </div> <% } %>  
  <% if (data.donnee[i].priorite == 0) {  %> <div class="li_pr0"> </div> <% } else { %>  <div class="li_pr0 enfonce"> </div> <% } %>                
  </div>

  <div class="categorie">
  <div class="li_bleu"> </div>
  <div class="li_jaune"> </div> 
  <div class="li_rose"> </div>
  <div class="li_vert"> </div>
  <div class="li_violet"> </div>
  <div class="li_blanc"> </div>
  </div>

  </div>

  </li>
  <% } %>
  </ul>                             
  </script>

  <script type="text/template" id="tmpl_legende">  
    <% for (var i = 0 ; i < legende.length; i++ ) {  %>   
      <div class="fil" id="f_<%= legende[i].id_couleur %>"> </div>    <div class="til" id="lib_<%= legende[i].id_couleur %>" ><%= legende[i].legende %></div>
    <% } %>   
  </script>