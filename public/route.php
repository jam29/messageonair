<!DOCTYPE html>
<head>
<script src="js/jquery-1.9.1.js"></script>
<script src="js/jquery.cookie.js"></script>
<script>
if (!$.cookie('salle')) { location.href='gestion_salle.php' }
else 
if (!$.cookie('role')) { 
	location.href='liste_session.php?salle='+$.cookie('salle')
} else {
	location.href=$.cookie('role')+'.php?session_sms='+$.cookie('session_sms')
}
</script>
</head>
</html>
