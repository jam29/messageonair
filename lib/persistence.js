var util = require('util');
var _ = require('lodash');
var connection = require('./mysql');
var moment      = require('moment');
var conf = require('../config');

exports.newmail = function(mail,callback) {

// console.log("MAIL",mail);
 var now   = moment().format("YYYY-MM-DD HH:mm:ss")
 var quand_ts = moment(mail.quand_ts).format("YYYY-MM-DD HH:mm:ss")
 var breaker = {}

// cas du mail, si le mail existe dans la table session_sms
var query= "SELECT id FROM session_sms WHERE email_reception = ? AND ( date_heure_debut_sms >= ? AND date_heure_fin_sms <= ? ) ";

connection.query(query,[mail.to_address,quand_ts,quand_ts],function(err,rows) { 
  if (err) { console.log(err); }
  
  if ( rows.length > 0 ) {
   var query="INSERT INTO messages VALUES('',?,?,'',?,?,?,?,'blanc',3,9999,0,0,?)";
    connection.query(query,[rows[0].id, mail.to_adress, mail.from_address ,now ,mail.texte , mail.texte,quand_ts ],function(err) {
      callback({id:rows[0].id})
    })
  
  } else {
  // console.log("DBNEWMAIL-TPH:",mail)
  // preg no tph
    var tel_regexp = new RegExp(/\d{11}/)
    if (tel_regexp.test(mail.to_name)) { 
        // recherche du numero de tph dans la fourchette horaire
        var query2 = "SELECT id from session_sms WHERE no_reception = ? AND ( ? >= date_heure_debut_sms AND ? <= date_heure_fin_sms )";
        connection.query(query2,[mail.to_name,quand_ts,quand_ts],function(err,rows2) {
          if ( rows2.length > 0 ) {
            var query="INSERT INTO messages VALUES('',?,?,?,'',?,?,?,'blanc',3,9999,0,0,?)" ; 
            connection.query(query,[rows2[0].id,mail.to_name, mail.from_name, now , mail.texte, mail.texte, quand_ts] , function(err) {
              if (err) { console.log(err) }
                callback({id:rows2[0].id})
              })  
          } else {
           // numero de tph hors fenêtre de temps
           var query="INSERT INTO messages VALUES('',99999,?,'',?,?,?,?,'blanc',3,9999,0,0,?)" ;
           connection.query(query,[mail.to_address , mail.from_address , now , mail.texte , mail.texte, quand_ts ] , function(err) {
             console.log(err) ;
           })
         }
      }) //query2

      } else {
      // ce n'est pas un tph
      var query="INSERT INTO messages VALUES('',99999,?,'',?,?,?,?,'blanc',3,9999,0,0,?)";
      connection.query(query,[mail.to_address,mail.from_address,now,mail.texte,mail.texte,quand_ts],function(err) {
        console.log(err)
      })
    }

 }

}) // connection query1

} // exports.newmail


exports.newFormulaire = function(data,callback) {
// var question = connection.escape(data.question);
 var now = moment().format("YYYY-MM-DD HH:mm:ss")
 //var query="INSERT INTO messages VALUES('',"+data.id_session+",'direct','direct','direct','"+now+"',"+question+","+question+",'blanc',3,9999,0,0,'"+now+"')"
 var query="INSERT INTO messages VALUES('', ? ,'direct','direct','direct',?,?,?,'blanc',3,9999,0,0,?)";
 connection.query(query,[data.id_session,now,data.question,now],function(err,result) {
   if(err) console.log(err)
     callback(result.insertId)
 }
 )
}

exports.allMessages = function(id_session,callback) {
  //var query = 'SELECT messages.* FROM messages WHERE id_session_sms='+id_session.id+' ORDER BY ts DESC'
  var query = 'SELECT messages.* FROM messages WHERE id_session_sms=? ORDER BY ts DESC';
  connection.query(query,[id_session.id],function(err,rows,fields){
    callback(null, { donnee:rows })
  })
}

exports.allMessagesLus = function(id_session,callback) {
  // var query = 'SELECT messages.* FROM messages WHERE id_session_sms = '+id_session.id+' and flag=3 ORDER BY ts_diffusion DESC'
  var query = 'SELECT messages.* FROM messages WHERE id_session_sms = ? and flag = 3 ORDER BY ts_diffusion DESC';
  connection.query(query,[id_session.id],function(err,rows,fields){
     if (err) { console.log(err); }
    callback(null, { donnee:rows })
  })
}

exports.allMessages2 = function(data,callback) {
 // var q = 'SELECT * FROM messages WHERE id_session_sms='+data.id+' AND priorite = 2 AND (flag = 0 OR flag = 1 ) ORDER by ordre ASC,ts ASC'
 var q = 'SELECT * FROM messages WHERE id_session_sms=? AND priorite = 2 AND (flag = 0 OR flag = 1 ) ORDER by ordre ASC,ts ASC'
  connection.query(q,[data.id],function(err,rows2,fields) {
    if(rows2[0]) {
      connection.query('UPDATE messages SET flag = 1 WHERE id_session_sms = ? AND id = ?',[ data.id , rows2[0].id] , function(err){})
    }
    callback(null,{donnee:rows2})
  })
}

exports.byRubrique = function(id_session,rubrique,callback) {
  //var cat = connection.escape(rubrique.categorie)
  connection.query('SELECT * FROM messages WHERE id_session_sms=? AND categorie= ?',[id_session.id, rubrique.categorie], function(err,rows,fields){
    callback(null, { donnee:rows })
  })	
}

exports.byPriorite = function(id_session,priorite,callback) {
 //var tph = connection.escape(priorite.tel)
 connection.query('SELECT * FROM messages WHERE id_session_sms = ? AND priorite= ?', [ id_session.id, priorite.priorite ] ,function(err,rows,fields){
  callback(null, { donnee:rows })
})  
}

exports.changeColor = function(data) {
  //var cat = connection.escape(data.newcolor)
  connection.query("UPDATE messages SET categorie = ? WHERE id = ?",[ data.newcolor, data.id ],function(err){
    if (err) {console.log(err)}
  } )
}

exports.changePriorite = function(data) {
 // connection.query("UPDATE messages SET priorite="+data.priorite+" WHERE id ="+data.id,function(err){
  
  connection.query("UPDATE messages SET priorite= ?  WHERE id = ? ",[ data.priorite, data.id ],function(err) {

    if (err) { console.log(err) }
  } )
}

exports.changeMessage = function(data) {
  
  // var mess = connection.escape(data.message)
  // connection.query("UPDATE messages SET message_a_editer="+mess+" WHERE id ="+data.id,function(err){
  connection.query("UPDATE messages SET message_a_editer = ? WHERE id = ? ",[ data.message, data.id ] , function(err){  
    if (err) { console.log(err) }
  } )
}

exports.changeFlag = function(data,callback) {
// passe l'ancien "en diffusion" en "diffusé" quand recoit un NEXT.

connection.query("UPDATE messages SET flag = 3 WHERE flag = 2 AND id_session_sms = ? " , [ data.session_id ] ,function(err){
 connection.query("UPDATE messages SET flag = 2, ts_diffusion = NOW() WHERE id = ? " , [ data.id ], function(err){  
  connection.query("UPDATE messages SET flag = 1 WHERE id = ?", [ data.next ], function(err){ if(err){console.log(err)};
    callback() } )
    })
  })
}

exports.changeFlaglus = function(data,callback) {
// passe l'ancien "en diffusion" en "diffusé" quand recoit un NEXT.
// console.log("DATALUS:",data)
connection.query("UPDATE messages SET flag = 3 WHERE flag = 2 AND id_session_sms = ?", [ data.session_id ] ,function(err){
   if (err) { console.log( err ); }
   connection.query("UPDATE messages SET flag = 2, ts_diffusion = NOW() WHERE id = ?", [ data.id ] , function(err){  
    if (err) { console.log(err) ; }
   callback()
 })
})
}


exports.changeFlagNext = function(data) {
// passe l'ancien "en diffusion" en "diffusé" quand recoit un NEXT 
   console.log("flagnext");
    connection.query("UPDATE messages SET flag=0 WHERE flag = 1 AND id_session_sms = ? ",[data.id_session] ,function(err){
      connection.query("UPDATE messages SET flag = 1 WHERE id =? ", [ data.idnext ], function(err){  
      }) 
    })
}

exports.changeTitreSession = function(data) {
 // var val = connection.escape(data.valeur)
  connection.query("UPDATE session_sms SET titre_session_sms1 = ? WHERE id = ? ",[ data.valeur, data.id ],function(err){
    if (err) { console.log(err) }
  } )
}

exports.changeDateSession = function(data) {
// var val = connection.escape(data.valeur)
 connection.query("UPDATE session_sms SET date= ? WHERE id = ?",[data.valeur, data.id] , function(err){
  if (err) { console.log(err) }
} )
}

exports.changeHeureDebut = function(data) {
 // var val = connection.escape(data.valeur)
  connection.query("UPDATE session_sms SET heure_debut= ? WHERE id = ? ",[data.valeur,data.id],function(err) {
    if (err) { console.log(err) }
  } )
}

exports.changeHeureFin = function(data) {
  //var val = connection.escape(data.valeur)
  connection.query("UPDATE session_sms SET heure_fin = ? WHERE id = ? ",[ data.valeur, data.id],function(err){
    if (err) { console.log(err) }
  } )
}

exports.deleteSession = function(data) {
  connection.query("DELETE FROM session_sms WHERE id =?",[data.id],function(err){
    if (err) { console.log(err) }
  } )
}

exports.addSession = function(data) {
  //var code = connection.escape(data.code)
  connection.query("INSERT INTO session_sms (code_session_sms,id_salle,titre_session_sms1,date,heure_debut,heure_fin) VALUES(?,?,'new session',CURRENT_DATE,CURRENT_TIME,CURRENT_TIME)",[ data.code , data.id_salle ] , function(err){
    if (err) { console.log(err) }
  } )
}

exports.getNomSalle = function(data,callback) {
  connection.query("SELECT nom FROM salle WHERE id =?",[data.id_salle], function(err){
    if (err) { console.log(err) }
      callback(null, { nom_salle:rows })
  } )
}


exports.changeNomSalle = function(data) {
  //var val = connection.escape(data.valeur)
  connection.query("UPDATE salle SET nom=? WHERE id =?",[data.valeur, data.id] , function(err){
    if (err) { console.log(err) }
  } )
}

exports.deleteSalle = function(data) {
  connection.query("DELETE FROM salle WHERE id =?",[data.id],function(err){
    if (err) { console.log(err) }
  } )
}

exports.addSalle = function(data) {
//  var salle = connection.escape(data.salle)
  connection.query("INSERT INTO salle (nom) VALUES(?)",[data.salle],function(err){
    if (err) { console.log(err) }
  } )
}

exports.changeOrdre = function(data) {
  connection.query("UPDATE messages SET ordre= ? WHERE id = ? ", [ data.ordre, data.id ],function(err){
    if (err) { console.log(err) }
  })
}

exports.onair = function(id_session,callback) {
  var q="SELECT * from messages WHERE id_session_sms = ? AND flag = 2 LIMIT 1"
  connection.query(q,[id_session.id_session],function(err,rows) {
    if ( err ) { console.log(err) }
      var value ="pas de message en cours de diffusion";
    if ( rows.length > 0 ) { value = rows[0] }
      callback(null, {encours:value,id_session:id_session.id_session} )
  })
}

exports.onairSalle = function(data,callback) {
  // recherche de le session en cours de la salle puis selection du message onair (flag=2)

  var now = moment().format("YYYY-MM-DD HH:mm:ss")
  var q =  "SELECT * FROM session_sms WHERE id_salle = ? AND ? >= date_heure_debut_sms AND ? <= date_heure_fin_sms LIMIT 1"

  connection.query(q,[ data.salle, now, now  ],function(err,rows){
    if ( err ) { console.log(err) }
      var q2="SELECT * from messages WHERE id_session_sms = ? AND flag = 2 LIMIT 1"
    connection.query(q2,[rows[0].id] , function( err , rows2 ) {
      if ( err ) { console.log(err) }
        var value ="pas de message en cours de diffusion";
      if ( rows2.length > 0 ) { value = rows2[0] }
        callback(null, value )
    })
  })
}


exports.thenext = function(id_session,callback) {
  connection.query("SELECT * from messages WHERE id_session_sms = ? AND  flag = 1 LIMIT 1",[id_session.id_session],function(err,rows){
    if (err) { console.log(err) }
      var value ="pas de message en cours de diffusion";
    if (rows.length > 0) { value = rows[0] }
      callback(null, {encours:value,id_session:id_session.id_session} )
  })
}


exports.getLegende = function(id_session,callback) {
 var query = "SELECT * FROM legende WHERE id_session_sms = ?";
 connection.query(query,[id_session.id_session],function(err,rows){
  if (err) { console.log(err) }
    callback(null, rows )
})}

/*
 exports.getSessions = function(data,callback) {
   var now = moment().format("YYYY-MM-DD HH:mm:ss")
  connection.query("SELECT * FROM session_sms WHERE id_salle="+data.id_salle+" AND '"+now+"' >= date_heure_debut_sms AND '"+now+"' <= date_heure_fin_sms " ,function(err,rows){
    if (err) { console.log(err) }
    callback(null, rows )
  })}
*/

exports.getSessions = function(data,callback) {
 //var now = moment().format("YYYY-MM-DD HH:mm:ss")
 var query = "SELECT session_sms.*,salle.nom FROM session_sms,salle WHERE session_sms.id_salle = ? AND salle.id = ? ";
 connection.query(query,[data.id_salle , data.id_salle] ,function(err,rows){
  if (err) { console.log(err) }
    callback(null, rows )
})}

 exports.getSessionsNow = function(callback) {
  var now = moment().format("YYYY-MM-DD HH:mm:ss")
  connection.query("SELECT session_sms.*,salle.nom FROM session_sms,salle WHERE ? >= date_heure_debut_sms AND ? <= date_heure_fin_sms AND id_salle=salle.id",[now,now] ,function(err,rows){
    if (err) { console.log(err) }
      callback(null, rows )
  })}

  exports.getOneSession = function(data,callback) {
    connection.query("SELECT session_sms.*,salle.nom FROM session_sms,salle WHERE session_sms.id = ? AND salle.id = session_sms.id_salle",[data.id],function(err,rows){
      if (err) { console.log(err) }
        callback(null, rows[0] )
    })}

    exports.getSalles = function(callback) {
      connection.query("SELECT * FROM salle ",[],function(err,rows){
        if (err) { console.log(err) }
          callback(null, rows )
      })}

      exports.getEmails = function(callback) {
        connection.query("SELECT * FROM email ",[],function(err,rows){
          if (err) { console.log(err) }

            callback(null, rows )
        })}

        exports.changeLegende = function(data) {
         // var id_couleur = connection.escape(data.id_couleur)
          //var legende = connection.escape(data.legende)
          var query = "UPDATE legende SET legende = ? WHERE id_session_sms = ? AND id_couleur = ?"
          connection.query(query,[data.legende,data.id_session,data.id_couleur] , function(err) {
            if (err) { console.log(err) }
          })  
        }

        exports.changeTsFin = function(data) {
          var new_ts = moment().add(5,'m');
          var nts = moment(new_ts).format("YYYY-MM-DD HH:mm:ss");
          var query  ="UPDATE session_sms SET date_heure_fin_sms = ? WHERE id = ?"; 
         // console.log(nts);
          connection.query(query,[ nts , data.id ],function(err) {
            if (err) { console.log(err) }
          })

        }

