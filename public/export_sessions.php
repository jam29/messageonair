<?php
// Encodage utf-8 : çàéèç
require_once('config.php');

$mysqli = new mysqli(DBSERVER, DBUSER, DBPASSWORD, DBNAME);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$mysqli->set_charset("utf8");
?>
<!doctype html>

<html lang="fr">
  <head>
    <meta charset="utf-8">
    <title>List des sessions</title>
    <link rel="stylesheet" href="css/export.css?v=1.0">
    <!--[if lt IE 9]>
      <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
    <h1>Obtenir les questions posées en session</h1>
    <table>
      <thead>
        <tr><th>Code</th><th>Salle</th><th>Date</th><th>Heure</th><th>Titre</th><th>Répertoire</th></tr>
      </thead>
      <tbody>
<?php
if ($result = $mysqli->query("SELECT session_sms.*,salle.nom FROM session_sms,salle WHERE session_sms.id_salle = salle.id ORDER BY code_session_sms")) {
  $sessions = $result->fetch_all(MYSQLI_ASSOC);
  foreach($sessions as $s) {
?>
        <tr><td><a href="export_excel.php?sessionId=<?php echo $s['id']; ?>"><?php echo $s['code_session_sms']; ?></a></td><td><?php echo $s['nom']; ?></td><td><?php echo $s['date']; ?></td><td><?php echo $s['heure_debut']; ?></td><td><?php echo $s['titre_session_sms1']; ?></td><td><a target="frm" href="http://localhost/preview5/opendir.php?sessionCode=<?php echo $s['code_session_sms']; ?>">DIR</a></td></tr>
<?php
  }
  $result->free();
}
$mysqli->close();
?>
      </tbody>
    </table>
    <iframe id="frm" name="frm" style="display:none;"></iframe>
    <!--  <script src="js/scripts.js"></script> -->
  </body>
</html>