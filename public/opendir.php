<?php
if(!is_dir('P:\\QUESTIONS_REPONSES\\'.$_REQUEST['sessionCode'])) {
	mkdir('P:\\QUESTIONS_REPONSES\\'.$_REQUEST['sessionCode']);
}
$command='explorer P:\\QUESTIONS_REPONSES\\'.$_REQUEST['sessionCode'];
$descriptorspec = array(
   0 => array("pipe", "r"),  // stdin est un pipe où le processus va lire
   1 => array("pipe", "w"),  // stdout est un pipe où le processus va écrire
   2 => array("file", "error-output.txt", "a"), // stderr est un fichier
);
$process = proc_open($command, $descriptorspec, $pipes);
//$process = proc_open("notepad.exe", $descriptorspec, $pipes);
proc_close($process);
//header("location:parcours_repertoire_salle.php?repertoire=$repertoire");
?>
