var util = require('util') ;
var express = require('express') 
, http = require('http')
, path = require('path') ;
var bodyParser = require('body-parser');
var methodOverride = require('method-override');
var socket =require('socket.io') ;
var db = require('./lib/persistence') ;
var MailListener = require("mail-listener2") ;
var conf=require('./config') ;

var app = express();
var server = http.createServer(app);
var io=require('socket.io')(server, {
  'path': conf.url_nodejs_root + 'socket.io'
});

db.getEmails( function(err,emails) {
  emails.forEach ( function connect(email) {
    var listener = new MailListener({
      username: email.username ,
      password: email.password,
      host: email.host,
      port: email.port,
      tls:email.tls,
      tlsOptions: { rejectUnauthorized: false },
      mailbox: email.mailbox,
      markSeen: true,
      fetchUnreadOnStart: true
});

//    console.log("LISTENER:",listener);
//    listener.imap._sock.socket.addListener('error', function(socketException){
//    if (socketException.errno === 61 /*ECONNREFUSED*/) {
//        console.log('ECONNREFUSED: connection refused to');
//      } else {
//        console.log(socketException);
//      }
//    });

    listener.start();

    listener.on("server:connected", function() {
      console.log("imap Connected "+email.username) 
    })

    listener.on("server:disconnected", function(){
      console.log("imap Disconnected "+email.username);
      connect(email);
    })

    listener.on("mail", function(mail) { 
      var from_address  = mail.from[0].address
      var from_name     = mail.from[0].name
      var to_address    = mail.to[0].address
      var to_name       = mail.to[0].name 
      var texte         = mail.text
      var quand_ts      = mail.headers.date
      
      db.newmail( { from_address:from_address , from_name:from_name , to_address:to_address , to_name:to_name , texte:texte , quand_ts:quand_ts, emails:emails } , function(id) {
        io.sockets.emit( 'new:sms', id)
      })  
    })
  })
 })
    

io.on('connection',function(socket) {

  console.log("-- client connecté")
   
   socket.on('diffusion:tph',function(data,callback){
       // recuperation éventuelle du numéro de tph associé à la session.
        db.getOneSession( { id:data.id } , function(err,session) { callback(session) })
   })

  socket.on('diffusion:conflit',function(data) {
      socket.broadcast.emit('diffusion:conflit',{data:data})
  }) 


 socket.on('diffusion:conflit_hide',function(data) {
      console.log("server app.js:conflit_hide");
      socket.broadcast.emit('diffusion:conflit_hide',{data:data}) ;
  } ) 

  socket.on('gestion:salles',function() { 
    db.getSalles(function(err,salles) {
      socket.emit('salles:all', { salles:salles } )
    })
  })

  socket.on('gestion:sessions',function(data) { 
    db.getSessions(data,function(err,sessions) {
      socket.emit('sessions:all', { sessions:sessions } )
    })
  })

  socket.on('gestion:sessions_now',function() { 
    db.getSessionsNow(function(err,sessions) {
      socket.emit('sessions:now', { sessions:sessions } )
    })
  })
  
  socket.on('moderateur1:connecte',function(data) { 
    socket.join('moderateurs1') 
    db.getLegende(data,function(err,legende) {
      socket.emit('legende:get',{ legende:legende} )
    })
  })

  socket.on('moderateur2:connecte',function(data) { 
   socket.join('moderateurs2') 
    db.onair(data,function(err,dataret) {
      socket.emit( 'messageonair', {message:dataret.encours.message_a_editer,session_id:dataret.encours.id_session_sms} )
    }) 

    db.getLegende(data,function(err,legende) {
      socket.emit('legende:get',{ legende:legende} )
    })        
  })

  socket.on('close:sms',function(data){
    socket.broadcast.emit('close:sms',data)
  })

  socket.on('sms:off',function(data){
    socket.broadcast.emit('sms:off',data)
  })

  socket.on('diffusion:connecte',function(data) { 
   var laSalle = data.salle
   socket.join(laSalle)
   db.onairSalle({salle:laSalle},function(err,retour) {
     io.sockets.in(laSalle).emit('messageonair',{ message:retour.message_a_editer, session_id:retour.id_session_sms} )
   })         
 })

  socket.on('animateur:connecte',function(animateurs) { 
   socket.join(animateurs)
   db.onair({ id_session:data.id_session},function(err,data) {
     socket.emit( 'messageonair' , { message:data.encours.message_a_editer,id_session:data.id_session} )
   }) 
   db.thenext({id_session:datas.id_session},function(err,data) {
     socket.emit( 'messagenextonair' , {message:data.encours.message_a_editer,id_session:data.id_session} )
   })         
 })

  socket.on('sms:new',function() { io.sockets.emit('mail') })

  socket.on('sms:all',function(session_id) {
   db.allMessages(session_id,function(err,data){ 
    socket.emit('sms:all',{ data:data })
  }) 
 })

  socket.on('sms:all2',function(data) {
   db.allMessages2(data,function(err,data){ 
      // console.log('DATA2',data);
     socket.emit('sms:all',{ data:data })
   }) 
 })

  socket.on('sms:lus',function(session_id) {
   db.allMessagesLus(session_id,function(err,data){ 
     // console.log("SMS-LUS",data)
     socket.emit('sms:all',{ data:data })
   }) 
 })

  socket.on('sms:rubrique',function(rub) {
   db.byRubrique(rub,function(err,data){ 
     socket.emit('sms:all',{ data:data })
   }) 
 })

  socket.on('sms:priorite',function(pr) {
   db.byPriorite(pr,function(err,data){ 
     socket.emit('sms:all',{ data:data })
   }) 
 })

  socket.on('change:color',function(data){
   db.changeColor(data)
   io.sockets.emit('change:color',data)    
 })

  socket.on('change:priorite',function(data) {
    console.log("changePriorite",data)

    db.changePriorite(data)  
    io.sockets.emit('change:priorite',data)  
 })  

  socket.on('change:message',function(data){
    db.changeMessage(data)
    io.sockets.emit('change:message',data)     
  })  

  socket.on('sms:onair', function(data,callback) { 
    console.log("SMS:ONAIR");   
    db.changeFlag(data,function() {
     db.allMessages2({id:data.session_id},function(err,ret_data){ 
      socket.emit( 'sms:all' , {data:ret_data} )  
    })
   })
    callback()
  })

 socket.on('sms:onairlus', function(data,callback) {  
  console.log("SMS:ONAIRLUS");     
    db.changeFlaglus(data,function() {
      
      db.allMessages2({id:data.session_id},function(err,ret_data){ 
       socket.emit( 'sms:all' , {data:ret_data} )
          callback()
       })
     
  })
   // callback()
  })

  
  socket.on('sms:messagenextonair', function(data) {  
    console.log("SMS:ONAIRNEXT");    
    if (!data.messagesLus) { 
      db.changeFlagNext(data) 
      io.sockets.emit('messagenextonair', data )
    }
  })

  socket.on('open:sms', function(data) { 
    // used by preview client
    console.log('emit diffusion salle:',data);
    io.sockets.emit('messageonair', data )
  })

  socket.on('change:ordre', function(data) {
    var tab = (data.sequence).split('&')
    for (var i=0 ; i < tab.length ; i++) {
      var lid = tab[i].substring(5)
      db.changeOrdre({ordre:i+1,id:lid})

    }
  })

  socket.on('legende:change', function(data) {
    db.changeLegende({session_id:data.session_id, id_couleur:data.id, legende:data.value})
  }
  )

  socket.on('change:titre_session', function(data) {

    db.changeTitreSession({ id:data.id, valeur:data.value })
  }
  )         

  socket.on('change:ts_debut_sms', function(data) {
    db.changeHeureDebut({id:data.id,valeur:data.value})
  }
  )

  socket.on('change:ts_fin_sms', function(data) {
    db.changeHeureFin({id:data.id,valeur:data.value})
  })

  socket.on('delete:session', function(data) {
    db.deleteSession({id:data.id})
    db.getSessions(data,function(err,sessions) {
      socket.emit('sessions:all', { sessions:sessions } )
    })
  })

  socket.on('ajouter:session',function(data){

    db.addSession(data)
    db.getSessions(data,function(err,sessions) {
      socket.emit('sessions:all', { sessions:sessions } )
    })
  })

  socket.on('get:session',function(data,callback){
    db.getOneSession({id:data.id},function(err,session){callback(session)})
  })

  socket.on('change:nom_salle', function(data) {
    db.changeNomSalle({id:data.id,valeur:data.value})
  })

  socket.on('delete:salle', function(data) {
    db.deleteSalle({id:data.id})
    db.getSalles(function(err,salles) {
      socket.emit('salles:all', { salles:salles } )
    })
  })

  socket.on('ajouter:salle',function(data){
    db.addSalle({salle:data.salle})
    db.getSalles(function(err,salles) {
      socket.emit('salles:all', { salles:salles } )
    })
  })

  socket.on('formulaire:question',function(data,callback) {
   db.newFormulaire(data,function(id) {

    io.sockets.emit('new:sms',{ id:data.id_session })
  })
   callback('efface')
 })

  socket.on('change:ts_fin',function(data){

    db.changeTsFin(data)
  })

})

// all environments
// io.enable('browser client etag');
// io.set('resource', conf.url_nodejs_root + 'socket.io');
//        io.set('transports', [
//                'websocket',
//              'flashsocket',
//              'htmlfile',
//              'xhr-polling',
//                'jsonp-polling'
//        ]);
// all environments

app.set('port', process.env.PORT || conf.PORT);
app.set('views', __dirname + '/views');
app.set('view engine', 'ejs');
app.use(express.favicon());
app.use(express.logger('dev'));

//app.use(express.bodyParser());
app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());

//app.use(express.methodOverride());
app.use(methodOverride());

app.use(express.static(path.join(__dirname, 'public')));
app.use(express.cookieParser());
app.use(express.cookieSession({ secret: 'secret', cookie: { maxAge: 60 * 60 * 1000 }}));
app.use(app.router);
// ... your middleware and routes

// development only
if ('development' == app.get('env')) {
  app.use(express.errorHandler());
}

app.get('/', function(req,res){

});

server.listen(app.get('port'), conf.server, function(){
  console.log('Express server listening on ' + conf.server + ' at port ' + app.get('port'));
});
