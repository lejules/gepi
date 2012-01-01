<?php
/*
*
* Copyright 2001-2011 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
*
* This file is part of GEPI.
*
* GEPI is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* GEPI is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with GEPI; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


// Initialisations files
require_once("../lib/initialisations.inc.php");

// Resume session
$resultat_session = $session_gepi->security_check();
if ($resultat_session == 'c') {
	header("Location: ../utilisateurs/mon_compte.php?change_mdp=yes");
	die();
} else if ($resultat_session == '0') {
	header("Location: ../logout.php?auto=1");
	die();
}

// Check access
if (!checkAccess()) {
	header("Location: ../logout.php?auto=1");
	die();
}

$msg = '';
if (isset($_POST['sup_logo'])) {
	check_token();

	$dest = '../images/';
	$ok = false;
	if ($f = @fopen("$dest/.test", "w")) {
		@fputs($f, '<'.'?php $ok = true; ?'.'>');
		@fclose($f);
		include("$dest/.test");
	}
	if (!$ok) {
		$msg = "Problème d'écriture sur le répertoire. Veuillez signaler ce problème à l'administrateur du site";
	} else {
		$old = getSettingValue("logo_etab");
		if (($old != '') and (file_exists($dest.$old))) unlink($dest.$old);
		$msg = "Le logo a été supprimé.";
		if (!saveSetting("logo_etab", '')) $msg .= "Erreur lors de l'enregistrement dans la table setting !";

	}

}

if (isset($_POST['valid_logo'])) {
	check_token();

	$doc_file = isset($_FILES["doc_file"]) ? $_FILES["doc_file"] : NULL;
	//if (ereg("\.([^.]+)$", $doc_file['name'], $match)) {
	//$match=array();
	//if (my_ereg("\.([^.]+)$", $doc_file['name'], $match)) {
	if (((function_exists("mb_ereg"))&&(mb_ereg("\.([^.]+)$", $doc_file['name'], $match)))||((function_exists("ereg"))&&(ereg("\.([^.]+)$", $doc_file['name'], $match)))) {
		$ext = my_strtolower($match[1]);
		if ($ext!='jpg' and $ext!='png'and $ext!='gif') {
		//if ($ext!='jpg' and $ext!='jpeg' and $ext!='png'and $ext!='gif') {
			$msg = "les seules extensions autorisées sont gif, png et jpg";
		} else {
			$dest = '../images/';
			$ok = false;
			if ($f = @fopen("$dest/.test", "w")) {
				@fputs($f, '<'.'?php $ok = true; ?'.'>');
				@fclose($f);
				include("$dest/.test");
			}
			if (!$ok) {
				$msg = "Problème d'écriture sur le répertoire IMAGES. Veuillez signaler ce problème à l'administrateur du site";
			} else {
				$old = getSettingValue("logo_etab");
				if (file_exists($dest.$old)) @unlink($dest.$old);
				if (file_exists($dest.$doc_file)) @unlink($dest.$doc_file);
				$ok = @copy($doc_file['tmp_name'], $dest.$doc_file['name']);
				if (!$ok) $ok = @move_uploaded_file($doc_file['tmp_name'], $dest.$doc_file['name']);
				if (!$ok) {
					$msg = "Problème de transfert : le fichier n'a pas pu être transféré sur le répertoire IMAGES. Veuillez signaler ce problème à l'administrateur du site";
				} else {
					$msg = "Le fichier a été transféré.";
				}
				if (!saveSetting("logo_etab", $doc_file['name'])) {
				$msg .= "Erreur lors de l'enregistrement dans la table setting !";
				}

			}
		}
	} else {
		$msg = "Le fichier sélectionné n'est pas valide !";
	}
}



if (isset($_POST['is_posted'])) {
	if ($_POST['is_posted']=='1') {
		check_token();



		// Max session length
		if (isset($_POST['sessionMaxLength'])) {
			if (!(my_ereg ("^[0-9]{1,}$", $_POST['sessionMaxLength'])) || $_POST['sessionMaxLength'] < 1) {
				$_POST['sessionMaxLength'] = 30;
			}
			if (!saveSetting("sessionMaxLength", $_POST['sessionMaxLength'])) {
				$msg .= "Erreur lors de l'enregistrement da durée max d'inactivité !";
			}
		}
		if (isset($_POST['gepiSchoolRne'])) {
			$enregistrer_gepiSchoolRne='y';
			if(($multisite=='y')&&(isset($_COOKIE['RNE']))) {
				if(($_POST['gepiSchoolRne']!='')&&(my_strtoupper($_POST['gepiSchoolRne'])!=my_strtoupper($_COOKIE['RNE']))) {
					$msg .= "Erreur lors de l'enregistrement du numéro RNE de l'établissement !<br />Le paramètre choisi risque de vous empêcher de vous connecter.<br />Enregistrement refusé!";
					$enregistrer_gepiSchoolRne='n';
				}
			}

			if($enregistrer_gepiSchoolRne=='y') {
				if (!saveSetting("gepiSchoolRne", $_POST['gepiSchoolRne'])) {
					$msg .= "Erreur lors de l'enregistrement du numéro RNE de l'établissement !";
				}
			}
		}
		if (isset($_POST['gepiYear'])) {
			if (!saveSetting("gepiYear", $_POST['gepiYear'])) {
				$msg .= "Erreur lors de l'enregistrement de l'année scolaire !";
			}
		}
		if (isset($_POST['gepiSchoolName'])) {
			if (!saveSetting("gepiSchoolName", $_POST['gepiSchoolName'])) {
				$msg .= "Erreur lors de l'enregistrement du nom de l'établissement !";
			}
		}
		if (isset($_POST['gepiSchoolStatut'])) {
			if (!saveSetting("gepiSchoolStatut", $_POST['gepiSchoolStatut'])) {
				$msg .= "Erreur lors de l'enregistrement du statut de l'établissement !";
			}
		}
		if (isset($_POST['gepiSchoolAdress1'])) {
			if (!saveSetting("gepiSchoolAdress1", $_POST['gepiSchoolAdress1'])) {
				$msg .= "Erreur lors de l'enregistrement de l'adresse !";
			}
		}
		if (isset($_POST['gepiSchoolAdress2'])) {
			if (!saveSetting("gepiSchoolAdress2", $_POST['gepiSchoolAdress2'])) {
				$msg .= "Erreur lors de l'enregistrement de l'adresse !";
			}
		}
		if (isset($_POST['gepiSchoolZipCode'])) {
			if (!saveSetting("gepiSchoolZipCode", $_POST['gepiSchoolZipCode'])) {
				$msg .= "Erreur lors de l'enregistrement du code postal !";
			}
		}
		if (isset($_POST['gepiSchoolCity'])) {
			if (!saveSetting("gepiSchoolCity", $_POST['gepiSchoolCity'])) {
				$msg .= "Erreur lors de l'enregistrement de la ville !";
			}
		}
		if (isset($_POST['gepiSchoolPays'])) {
			if (!saveSetting("gepiSchoolPays", $_POST['gepiSchoolPays'])) {
				$msg .= "Erreur lors de l'enregistrement du pays !";
			}
		}
		if (isset($_POST['gepiSchoolAcademie'])) {
			if (!saveSetting("gepiSchoolAcademie", $_POST['gepiSchoolAcademie'])) {
				$msg .= "Erreur lors de l'enregistrement de l'académie !";
			}
		}
		if (isset($_POST['gepiSchoolTel'])) {
			if (!saveSetting("gepiSchoolTel", $_POST['gepiSchoolTel'])) {
				$msg .= "Erreur lors de l'enregistrement du numéro de téléphone !";
			}
		}
		if (isset($_POST['gepiSchoolFax'])) {
			if (!saveSetting("gepiSchoolFax", $_POST['gepiSchoolFax'])) {
				$msg .= "Erreur lors de l'enregistrement du numéro de fax !";
			}
		}
		if (isset($_POST['gepiSchoolEmail'])) {
			if (!saveSetting("gepiSchoolEmail", $_POST['gepiSchoolEmail'])) {
				$msg .= "Erreur lors de l'adresse électronique !";
			}
		}
		if (isset($_POST['gepiAdminNom'])) {
			if (!saveSetting("gepiAdminNom", $_POST['gepiAdminNom'])) {
				$msg .= "Erreur lors de l'enregistrement du nom de l'administrateur !";
			}
		}
		if (isset($_POST['gepiAdminPrenom'])) {
			if (!saveSetting("gepiAdminPrenom", $_POST['gepiAdminPrenom'])) {
				$msg .= "Erreur lors de l'enregistrement du prénom de l'administrateur !";
			}
		}
		if (isset($_POST['gepiAdminFonction'])) {
			if (!saveSetting("gepiAdminFonction", $_POST['gepiAdminFonction'])) {
				$msg .= "Erreur lors de l'enregistrement de la fonction de l'administrateur !";
			}
		}
		
		if (isset($_POST['gepiAdminAdress'])) {
			if (!saveSetting("gepiAdminAdress", $_POST['gepiAdminAdress'])) {
				$msg .= "Erreur lors de l'enregistrement de l'adresse email !";
			}
		}

		if (isset($_POST['gepiAdminAdressPageLogin'])) {
			if (!saveSetting("gepiAdminAdressPageLogin", 'y')) {
				$msg .= "Erreur lors de l'enregistrement de l'affichage de adresse email sur la page de login !";
			}
		}
		else{
			if (!saveSetting("gepiAdminAdressPageLogin", 'n')) {
				$msg .= "Erreur lors de l'enregistrement du non-affichage de adresse email sur la page de login !";
			}
		}

		if (isset($_POST['contact_admin_mailto'])) {
			if (!saveSetting("contact_admin_mailto", 'y')) {
				$msg .= "Erreur lors de l'enregistrement de 'contact_admin_mailto' !";
			}
		}
		else {
			if (!saveSetting("contact_admin_mailto", 'n')) {
				$msg .= "Erreur lors de l'enregistrement de 'contact_admin_mailto' !";
			}
		}

		if (isset($_POST['envoi_mail_liste'])) {
			if (!saveSetting("envoi_mail_liste", 'y')) {
				$msg .= "Erreur lors de l'enregistrement de 'envoi_mail_liste' !";
			}
		}
		else {
			if (!saveSetting("envoi_mail_liste", 'n')) {
				$msg .= "Erreur lors de l'enregistrement de 'envoi_mail_liste' !";
			}
		}

		if (isset($_POST['gepiAdminAdressFormHidden'])) {
			if (!saveSetting("gepiAdminAdressFormHidden", 'n')) {
				$msg .= "Erreur lors de l'enregistrement de l'affichage de adresse email dans le formulaire [Contacter l'administrateur] !";
			}
		}
		else{
			if (!saveSetting("gepiAdminAdressFormHidden", 'y')) {
				$msg .= "Erreur lors de l'enregistrement du non-affichage de adresse email dans le formulaire [Contacter l'administrateur] !";
			}
		}



	
	
		if (isset($_POST['longmin_pwd'])) {
			if (!saveSetting("longmin_pwd", $_POST['longmin_pwd'])) {
				$msg .= "Erreur lors de l'enregistrement de la longueur minimale du mot de passe !";
			}
		}
		
		if (isset($_POST['mode_generation_pwd_majmin'])) {
			if (!saveSetting("mode_generation_pwd_majmin", $_POST['mode_generation_pwd_majmin'])) {
				$msg .= "Erreur lors de l'enregistrement du paramètre Min/Maj sur les mots de passe !";
			}
		}
	
		if (isset($_POST['mode_generation_pwd_excl'])) {
			if (!saveSetting("mode_generation_pwd_excl", $_POST['mode_generation_pwd_excl'])) {
				$msg .= "Erreur lors de l'enregistrement du paramètre d'exclusion des caractères prêtant à confusion sur les mots de passe !";
			}
		}
		else{
			if (!saveSetting("mode_generation_pwd_excl", 'n')) {
				$msg .= "Erreur lors de l'enregistrement du paramètre d'exclusion des caractères prêtant à confusion sur les mots de passe !";
			}
		}

		if (isset($_POST['mode_email_resp'])) {
			if (!saveSetting("mode_email_resp", $_POST['mode_email_resp'])) {
				$msg .= "Erreur lors de l'enregistrement du mode de mise à jour des email responsables !";
			}
			else {
				$sql="SELECT * FROM infos_actions WHERE titre='Paramétrage mode_email_resp requis';";
				$res_test=mysql_query($sql);
				if(mysql_num_rows($res_test)>0) {
					while($lig_ia=mysql_fetch_object($res_test)) {
						$sql="DELETE FROM infos_actions_destinataires WHERE id_info='$lig_ia->id';";
						$del=mysql_query($sql);
						if($del) {
							$sql="DELETE FROM infos_actions WHERE id='$lig_ia->id';";
							$del=mysql_query($sql);
						}
					}
				}

			}
		}

		if (isset($_POST['mode_email_ele'])) {
			if (!saveSetting("mode_email_ele", $_POST['mode_email_ele'])) {
				$msg .= "Erreur lors de l'enregistrement du mode de mise à jour des email élèves !";
			}
			else {
				$sql="SELECT * FROM infos_actions WHERE titre='Paramétrage mode_email_ele requis';";
				$res_test=mysql_query($sql);
				if(mysql_num_rows($res_test)>0) {
					while($lig_ia=mysql_fetch_object($res_test)) {
						$sql="DELETE FROM infos_actions_destinataires WHERE id_info='$lig_ia->id';";
						$del=mysql_query($sql);
						if($del) {
							$sql="DELETE FROM infos_actions WHERE id='$lig_ia->id';";
							$del=mysql_query($sql);
						}
					}
				}
			}
		}
	
		if (isset($_POST['type_bulletin_par_defaut'])) {
			if(($_POST['type_bulletin_par_defaut']=='html')||($_POST['type_bulletin_par_defaut']=='pdf')) {
				if (!saveSetting("type_bulletin_par_defaut", $_POST['type_bulletin_par_defaut'])) {
					$msg .= "Erreur lors de l'enregistrement du paramètre type_bulletin_par_defaut !";
				}
			}
			else {
				$msg .= "Valeur erronée pour l'enregistrement du paramètre type_bulletin_par_defaut !";
			}
		}
	
		if (isset($_POST['exp_imp_chgt_etab'])) {
			if (!saveSetting("exp_imp_chgt_etab", $_POST['exp_imp_chgt_etab'])) {
				$msg .= "Erreur lors de l'enregistrement du paramètre exp_imp_chgt_etab !";
			}
		}
		else{
			if (!saveSetting("exp_imp_chgt_etab", 'no')) {
				$msg .= "Erreur lors de l'enregistrement du paramètre exp_imp_chgt_etab !";
			}
		}
	
		if (isset($_POST['ele_lieu_naissance'])) {
			if (!saveSetting("ele_lieu_naissance", $_POST['ele_lieu_naissance'])) {
				$msg .= "Erreur lors de l'enregistrement du paramètre ele_lieu_naissance !";
			}
		}
		else{
			if (!saveSetting("ele_lieu_naissance", 'no')) {
				$msg .= "Erreur lors de l'enregistrement du paramètre ele_lieu_naissance !";
			}
		}
	
		if (isset($_POST['avis_conseil_classe_a_la_mano'])) {
			if (!saveSetting("avis_conseil_classe_a_la_mano", $_POST['avis_conseil_classe_a_la_mano'])) {
				$msg .= "Erreur lors de l'enregistrement du paramètre avis_conseil_classe_a_la_mano !";
			}
		}
		else{
			if (!saveSetting("avis_conseil_classe_a_la_mano", 'n')) {
				$msg .= "Erreur lors de l'enregistrement du paramètre avis_conseil_classe_a_la_mano !";
			}
		}
	
	
		//===============================================================
	
	
		// Dénomination du professeur de suivi
		if (isset($_POST['gepi_prof_suivi'])) {
			if (!saveSetting("gepi_prof_suivi", $_POST['gepi_prof_suivi'])) {
				$msg .= "Erreur lors de l'enregistrement de gepi_prof_suivi !";
			}
		}
		
		// Dénomination des professeurs
		if (isset($_POST['denomination_professeur'])) {
			if (!saveSetting("denomination_professeur", $_POST['denomination_professeur'])) {
				$msg .= "Erreur lors de l'enregistrement de denomination_professeur !";
			}
		}
		if (isset($_POST['denomination_professeurs'])) {
			if (!saveSetting("denomination_professeurs", $_POST['denomination_professeurs'])) {
				$msg .= "Erreur lors de l'enregistrement de denomination_professeurs !";
			}
		}
		
		// Dénomination des responsables légaux
		if (isset($_POST['denomination_responsable'])) {
			if (!saveSetting("denomination_responsable", $_POST['denomination_responsable'])) {
				$msg .= "Erreur lors de l'enregistrement de denomination_responsable !";
			}
		}
		if (isset($_POST['denomination_responsables'])) {
			if (!saveSetting("denomination_responsables", $_POST['denomination_responsables'])) {
				$msg .= "Erreur lors de l'enregistrement de denomination_responsables !";
			}
		}
		
		// Dénomination des élèves
		if (isset($_POST['denomination_eleve'])) {
			if (!saveSetting("denomination_eleve", $_POST['denomination_eleve'])) {
				$msg .= "Erreur lors de l'enregistrement de denomination_eleve !";
			}
		}
		if (isset($_POST['denomination_eleves'])) {
			if (!saveSetting("denomination_eleves", $_POST['denomination_eleves'])) {
				$msg .= "Erreur lors de l'enregistrement de denomination_eleves !";
			}
		}
		// Initialiser à 'Boite'
		if (isset($_POST['gepi_denom_boite'])) {
			if (!saveSetting("gepi_denom_boite", $_POST['gepi_denom_boite'])) {
				$msg .= "Erreur lors de l'enregistrement de gepi_denom_boite !";
			}
		}
		if (isset($_POST['gepi_denom_boite_genre'])) {
			if (!saveSetting("gepi_denom_boite_genre", $_POST['gepi_denom_boite_genre'])) {
				$msg .= "Erreur lors de l'enregistrement de gepi_denom_boite_genre !";
			}
		}

		if((isset($_POST['gepi_denom_mention']))&&($_POST['gepi_denom_mention']!="")) {
			if (!saveSetting("gepi_denom_mention", $_POST['gepi_denom_mention'])) {
				$msg .= "Erreur lors de l'enregistrement de gepi_denom_mention !";
			}
		}
		else {
			if (!saveSetting("gepi_denom_mention", "mention")) {
				$msg .= "Erreur lors de l'initialisation de gepi_denom_mention !";
			}
		}

		if (isset($_POST['gepi_stylesheet'])) {
			if (!saveSetting("gepi_stylesheet", $_POST['gepi_stylesheet'])) {
				$msg .= "Erreur lors de l'enregistrement de l'année scolaire !";
			}
		}
		
		if (isset($_POST['num_enregistrement_cnil'])) {
			if (!saveSetting("num_enregistrement_cnil", $_POST['num_enregistrement_cnil'])) {
				$msg .= "Erreur lors de l'enregistrement du numéro d'enregistrement à la CNIL !";
			}
		}

		if (isset($_POST['mode_generation_login'])) {
			if(!check_format_login($_POST['mode_generation_login'])) {
				$msg .= "Format de login invalide pour les personnels !";
			}
			else {
				if (!saveSetting("mode_generation_login", $_POST['mode_generation_login'])) {
					$msg .= "Erreur lors de l'enregistrement du mode de génération des logins personnels !";
				}
				else {
					$nbre_carac = mb_strlen($_POST['mode_generation_login']);
					$req = "UPDATE setting SET value = '".$nbre_carac."' WHERE name = 'longmax_login'";
					$modif_maxlong = mysql_query($req);
				}
			}
		}

		if (isset($_POST['mode_generation_login_casse'])) {
			if(($_POST['mode_generation_login_casse']!='min')&&($_POST['mode_generation_login_casse']!='maj')) {
				$msg .= "Casse invalide pour le format de login invalide pour les personnels !";
			}
			else {
				if (!saveSetting("mode_generation_login_casse", $_POST['mode_generation_login_casse'])) {
					$msg .= "Erreur lors de l'enregistrement de la casse du format de login des personnels !";
				}
			}
		}

		if (isset($_POST['mode_generation_login_eleve'])) {
			if(!check_format_login($_POST['mode_generation_login_eleve'])) {
				$msg .= "Format de login invalide pour les élèves !";
			}
			else {
				if (!saveSetting("mode_generation_login_eleve", $_POST['mode_generation_login_eleve'])) {
					$msg .= "Erreur lors de l'enregistrement du mode de génération des logins élèves !";
				}
				else {
					$nbre_carac = mb_strlen($_POST['mode_generation_login_eleve']);
					$req = "UPDATE setting SET value = '".$nbre_carac."' WHERE name = 'longmax_login_eleve'";
					$modif_maxlong = mysql_query($req);
				}
			}
		}

		if (isset($_POST['mode_generation_login_eleve_casse'])) {
			if(($_POST['mode_generation_login_eleve_casse']!='min')&&($_POST['mode_generation_login_eleve_casse']!='maj')) {
				$msg .= "Casse invalide pour le format de login invalide pour les élèves !";
			}
			else {
				if (!saveSetting("mode_generation_login_eleve_casse", $_POST['mode_generation_login_eleve_casse'])) {
					$msg .= "Erreur lors de l'enregistrement de la casse du format de login des élèves !";
				}
			}
		}

		if (isset($_POST['mode_generation_login_responsable'])) {
			if(!check_format_login($_POST['mode_generation_login_responsable'])) {
				$msg .= "Format de login invalide pour les responsables !";
			}
			else {
				if (!saveSetting("mode_generation_login_responsable", $_POST['mode_generation_login_responsable'])) {
					$msg .= "Erreur lors de l'enregistrement du mode de génération des logins responsables !";
				}
				else {
					$nbre_carac = mb_strlen($_POST['mode_generation_login_responsable']);
					$req = "UPDATE setting SET value = '".$nbre_carac."' WHERE name = 'longmax_login_responsable'";
					$modif_maxlong = mysql_query($req);
				}
			}
		}

		if (isset($_POST['mode_generation_login_responsable_casse'])) {
			if(($_POST['mode_generation_login_responsable_casse']!='min')&&($_POST['mode_generation_login_responsable_casse']!='maj')) {
				$msg .= "Casse invalide pour le format de login invalide pour les responsables !";
			}
			else {
				if (!saveSetting("mode_generation_login_responsable_casse", $_POST['mode_generation_login_responsable_casse'])) {
					$msg .= "Erreur lors de l'enregistrement de la casse du format de login des responsables !";
				}
			}
		}


		if (isset($_POST['unzipped_max_filesize'])) {
			$unzipped_max_filesize=$_POST['unzipped_max_filesize'];
			if(mb_substr($unzipped_max_filesize,0,1)=="-") {$unzipped_max_filesize=-1;}
			elseif(mb_strlen(my_ereg_replace("[0-9]","",$unzipped_max_filesize))!=0) {
				$unzipped_max_filesize=10;
				$msg .= "Caractères invalides pour le paramètre unzipped_max_filesize<br />Initialisation à 10 Mo !";
			}
		
			if (!saveSetting("unzipped_max_filesize", $unzipped_max_filesize)) {
				$msg .= "Erreur lors de l'enregistrement du paramètre unzipped_max_filesize !";
			}
		}


		if (isset($_POST['bul_rel_nom_matieres'])) {
			$bul_rel_nom_matieres=$_POST['bul_rel_nom_matieres'];
			if (!saveSetting("bul_rel_nom_matieres", $bul_rel_nom_matieres)) {
				$msg .= "Erreur lors de l'enregistrement du paramètre bul_rel_nom_matieres !";
			}
		}



		if (isset($_POST['delais_apres_cloture'])) {
			$delais_apres_cloture=$_POST['delais_apres_cloture'];
			if (!(my_ereg ("^[0-9]{1,}$", $delais_apres_cloture)) || $delais_apres_cloture < 0) {
				//$delais_apres_cloture=0;
				$msg .= "Erreur lors de l'enregistrement de delais_apres_cloture !";
			}
			else {
				if (!saveSetting("delais_apres_cloture", $delais_apres_cloture)) {
					$msg .= "Erreur lors de l'enregistrement de delais_apres_cloture !";
				}
			}
		}
		
		if (isset($_POST['acces_app_ele_resp'])) {
			$acces_app_ele_resp=$_POST['acces_app_ele_resp'];
			if (!saveSetting("acces_app_ele_resp", $acces_app_ele_resp)) {
				$msg .= "Erreur lors de l'enregistrement de acces_app_ele_resp !";
			}
		}



	}
}


if (isset($_POST['gepi_pmv'])) {
	check_token();

	if (!saveSetting("gepi_pmv", $_POST['gepi_pmv'])) {
		$msg .= "Erreur lors de l'enregistrement de gepi_pmv !";
	}
}


/*
if(isset($_POST['is_posted'])){
	if (isset($_POST['export_cn_ods'])) {
		//if (!saveSetting("export_cn_ods", $_POST['export_cn_ods'])) {
		if (!saveSetting("export_cn_ods", 'y')) {
			$msg .= "Erreur lors de l'enregistrement de l'autorisation de l'export au format ODS !";
		}
	}
	else{
		if (!saveSetting("export_cn_ods", 'n')) {
			$msg .= "Erreur lors de l'enregistrement de l'interdiction de l'export au format ODS !";
		}
	}
}
*/

// Load settings
if (!loadSettings()) {
	die("Erreur chargement settings");
}
if (isset($_POST['is_posted']) and ($msg=='')) $msg = "Les modifications ont été enregistrées !";


//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
$themessage  = 'Des informations ont été modifiées. Voulez-vous vraiment quitter sans enregistrer ?';
//**************** EN-TETE *****************
// End standart header
$titre_page = "Paramètres généraux";
require_once("../lib/header.inc");
//**************** FIN EN-TETE *****************

//debug_var();

?>

<div class="success">
  <p style="font-size: x-large;color: green;font-weight: bold;">Toutes les informations ont été mises à jour</p>
   <div></div>
</div> 

<p class=bold><a href="index.php#param_gen"<?php
echo " onclick=\"return confirm_abandon (this, change, '$themessage')\"";
?>><img src='../images/icons/back.png' alt='Retour' class='back_link'/> Retour</a></p>
<form action="param_gen.php" method="post" name="form1" class="horizontal_form">
<?php
echo add_token_field();
?>
<fieldset>
      <legend>L'établissement</legend>
      <ul>
        <li>
          <label for="annee">Année scolaire</label>
          <input type="text" name="gepiYear" class="requiredfield" id="annee" size="20" value="<?php echo(getSettingValue("gepiYear")); ?>" onchange='changement()' />
        </li>
        <li>
          <label for="rne">Numéro RNE</label>
          <input type="text" id="rne" name="gepiSchoolRne" class="requiredfield" size="8" value="<?php echo(getSettingValue("gepiSchoolRne")); ?>" onchange='changement()' />
          <div class="form_input_notes" id="name-notes">RNE ou UAI de l'établissement</div>
        </li>
      </ul>
</fieldset>
<!--<table style="width: 100%; border: 0;" cellpadding="5" cellspacing="5" summary='Paramètres'>-->
<table class="plain" width="100%">
	<tr>
		<td style="font-variant: small-caps;">
		Nom de l'établissement :
		</td>
		<td><input type="text" name="gepiSchoolName" size="20" value="<?php echo(getSettingValue("gepiSchoolName")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Statut de l'établissement :<br />
		(<span style='font-style:italic;font-size:x-small'>utilisé pour certains documents officiels</span>)
		</td>
		<td>
                    <select name='gepiSchoolStatut' onchange='changement()'>
			<option value='public'<?php if (getSettingValue("gepiSchoolStatut")=='public') echo " SELECTED"; ?>>établissement public</option>
			<option value='prive_sous_contrat'<?php if (getSettingValue("gepiSchoolStatut")=='prive_sous_contrat') echo " SELECTED"; ?>>établissement privé sous contrat</option>
			<option value='prive_hors_contrat'<?php if (getSettingValue("gepiSchoolStatut")=='prive_hors_contrat') echo " SELECTED"; ?>>établissement privé hors contrat</option>
                    </select>
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Adresse de l'établissement :
		</td>
		<td><input type="text" name="gepiSchoolAdress1" size="40" value="<?php echo(getSettingValue("gepiSchoolAdress1")); ?>" onchange='changement()' /><br />
		<input type="text" name="gepiSchoolAdress2" size="40" value="<?php echo(getSettingValue("gepiSchoolAdress2")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Code postal :
		</td>
		<td><input type="text" name="gepiSchoolZipCode" size="20" value="<?php echo(getSettingValue("gepiSchoolZipCode")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Ville :
		</td>
		<td><input type="text" name="gepiSchoolCity" size="20" value="<?php echo(getSettingValue("gepiSchoolCity")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Pays :<br />
		(<!--<span style='font-style:italic;font-size:x-small'>-->Le pays est utilisé pour comparer avec celui des responsables dans les blocs adresse des courriers adressés aux responsables<!--</span>-->)
		</td>
		<td><input type="text" name="gepiSchoolPays" size="20" value="<?php echo(getSettingValue("gepiSchoolPays")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Académie :<br />
		(<span style='font-style:italic;font-size:x-small'>utilisé pour certains documents officiels</span>)
		</td>
		<td><input type="text" name="gepiSchoolAcademie" size="20" value="<?php echo(getSettingValue("gepiSchoolAcademie")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Téléphone établissement :
		</td>
		<td><input type="text" name="gepiSchoolTel" size="20" value="<?php echo(getSettingValue("gepiSchoolTel")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Fax établissement :
		</td>
		<td><input type="text" name="gepiSchoolFax" size="20" value="<?php echo(getSettingValue("gepiSchoolFax")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		E-mail établissement :
		</td>
		<td><input type="text" name="gepiSchoolEmail" size="20" value="<?php echo(getSettingValue("gepiSchoolEmail")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Nom de l'administrateur du site :
		</td>
		<td><input type="text" name="gepiAdminNom" size="20" value="<?php echo(getSettingValue("gepiAdminNom")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Prénom de l'administrateur du site :
		</td>
		<td><input type="text" name="gepiAdminPrenom" size="20" value="<?php echo(getSettingValue("gepiAdminPrenom")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Fonction de l'administrateur du site :
		</td>
		<td><input type="text" name="gepiAdminFonction" size="20" value="<?php echo(getSettingValue("gepiAdminFonction")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Email de l'administrateur du site :
		</td>
		<td><input type="text" name="gepiAdminAdress" size="20" value="<?php echo(getSettingValue("gepiAdminAdress")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		<label for='gepiAdminAdressPageLogin' style='cursor: pointer;'>Faire apparaitre le lien [Contacter l'administrateur] sur la page de login :</label>
		</td>
		<td>
		<input type="checkbox" id='gepiAdminAdressPageLogin' name="gepiAdminAdressPageLogin" value="y"
		<?php
			if(getSettingValue("gepiAdminAdressPageLogin")!='n'){echo " checked";}
		?>
		onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		<label for='gepiAdminAdressFormHidden' style='cursor: pointer;'>Faire apparaitre l'adresse de l'administrateur dans le formulaire [Contacter l'administrateur] :</label>
		</td>
		<td>
		<input type="checkbox" name="gepiAdminAdressFormHidden" id="gepiAdminAdressFormHidden" value="n"
		<?php
			if(getSettingValue("gepiAdminAdressFormHidden")!='y'){echo " checked";}
		?>
		onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		<label for='contact_admin_mailto' style='cursor: pointer;'>Remplacer le formulaire [Contacter l'administrateur] par un lien mailto :</label>
		</td>
		<td>
		<input type="checkbox" id='contact_admin_mailto' name="contact_admin_mailto" value="y"
		<?php
			if(getSettingValue("contact_admin_mailto")=='y'){echo " checked";}
		?>
		onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		<label for='envoi_mail_liste' style='cursor: pointer;'>Permettre d'envoyer des mails à une liste d'élèves :<br />
		<span style='font-size: small'>(<i>sous réserve que les mails soient remplis</i>)</span><br />
		<span style='font-size: small'>Nous attirons votre attention sur le fait qu'envoyer un mail à une liste d'utilisateurs via un lien mailto permet à chaque élève de connaitre les email des autres élèves sans que l'autorisation de divulgation ou non paramétrée dans <b>Gérer mon compte</b> soit prise en compte.</span></label>
		</td>
		<td valign='top'>
		<input type="checkbox" id='envoi_mail_liste' name="envoi_mail_liste" value="y"
		<?php
			if(getSettingValue("envoi_mail_liste")=='y'){echo " checked";}
		?>
		onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		<a name='sessionMaxLength'></a>Durée maximum d'inactivité : <br />
		<span class='small'>(<i>Durée d'inactivité, en minutes, au bout de laquelle un utilisateur est automatiquement déconnecté de Gepi.</i>) <b>Attention</b>, la variable <b>session.maxlifetime</b> dans le fichier <b>php.ini</b> est réglée à <?php 
			$session_gc_maxlifetime=ini_get("session.gc_maxlifetime");
			$session_gc_maxlifetime_minutes=$session_gc_maxlifetime/60;

			if((getSettingValue("sessionMaxLength")!="")&&($session_gc_maxlifetime_minutes<getSettingValue("sessionMaxLength"))) {
				echo "<span style='color:red; font-weight:bold;'>".$session_gc_maxlifetime." secondes</span>, soit un maximum de <span style='color:red; font-weight:bold;'>".$session_gc_maxlifetime_minutes."minutes</span> pour la session (<a href='../mod_serveur/test_serveur.php#reglages_php'>*</a>).";
			}
			else {
				echo $session_gc_maxlifetime." secondes, soit un maximum de ".$session_gc_maxlifetime_minutes."minutes pour la session.";
			}
		?></span>
		</td>
		<td><input type="text" name="sessionMaxLength" id="sessionMaxLength" size="20" value="<?php echo(getSettingValue("sessionMaxLength")); ?>" onchange='changement()' onkeydown="clavier_2(this.id,event,1,600)" />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Longueur minimale du mot de passe :</td>
		<td><input type="text" name="longmin_pwd" id="longmin_pwd" size="20" value="<?php echo(getSettingValue("longmin_pwd")); ?>" onchange='changement()' onkeydown="clavier_2(this.id,event,1,50)" />
		</td>
	</tr>
		<?php 
			if (isset($use_custom_denominations) && $use_custom_denominations) {
		?>
	<tr>
		<td style="font-variant: small-caps;">
		Dénomination des professeurs :</td>
		<td>Sing. :<input type="text" name="denomination_professeur" size="20" value="<?php echo(getSettingValue("denomination_professeur")); ?>" onchange='changement()' />
		<br/>Pluriel :<input type="text" name="denomination_professeurs" size="20" value="<?php echo(getSettingValue("denomination_professeurs")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Dénomination des élèves :</td>
		<td>Sing. :<input type="text" name="denomination_eleve" size="20" value="<?php echo(getSettingValue("denomination_eleve")); ?>" />
		<br/>Pluriel :<input type="text" name="denomination_eleves" size="20" value="<?php echo(getSettingValue("denomination_eleves")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;">
		Dénomination des responsables légaux :</td>
		<td>Sing. :<input type="text" name="denomination_responsable" size="20" value="<?php echo(getSettingValue("denomination_responsable")); ?>" onchange='changement()' />
		<br/>Pluriel :<input type="text" name="denomination_responsables" size="20" value="<?php echo(getSettingValue("denomination_responsables")); ?>" onchange='changement()' />
		</td>
	</tr>
		<?php 
			} 
		?>
	<tr>
		<td style="font-variant: small-caps;">
		Dénomination du professeur chargé du suivi des élèves :</td>
		<td><input type="text" name="gepi_prof_suivi" size="20" value="<?php echo(getSettingValue("gepi_prof_suivi")); ?>" onchange='changement()' />
		</td>
	</tr>
	<tr>
		<td style="font-variant: small-caps;" valign='top'>
		Désignation des boites/conteneurs/emplacements/sous-matières :</td>
		<td>
		<input type="text" name="gepi_denom_boite" size="20" value="<?php echo(getSettingValue("gepi_denom_boite")); ?>" onchange='changement()' /><br />
		<table summary='Genre'><tr valign='top'><td>Genre :</td><td>
		<input type="radio" name="gepi_denom_boite_genre" id="gepi_denom_boite_genre_m" value="m" <?php if(getSettingValue("gepi_denom_boite_genre")=="m"){echo 'checked';} ?> onchange='changement()' /> <label for='gepi_denom_boite_genre_m' style='cursor: pointer;'>Masculin</label><br />
		<input type="radio" name="gepi_denom_boite_genre" id="gepi_denom_boite_genre_f" value="f" <?php if(getSettingValue("gepi_denom_boite_genre")=="f"){echo 'checked';} ?> onchange='changement()' /> <label for='gepi_denom_boite_genre_f' style='cursor: pointer;'>Féminin</label><br />
		</td></tr></table>
		</td>
	</tr>

	<tr>
		<td style="font-variant: small-caps;" valign='top'>
		<a name='gepi_denom_mention'></a>
		Désignation des "mentions" pouvant être saisies avec l'avis du conseil de classe :<br />
		(<i>terme au singulier</i>)<br />
		<a href='../saisie/saisie_mentions.php' <?php 
			echo "onclick=\"return confirm_abandon (this, change, '$themessage')\"";
		?>>Définir des "mentions"</a></td>
		<td>
		<input type="text" name="gepi_denom_mention" size="20" value="<?php
			
			$gepi_denom_mention=getSettingValue("gepi_denom_mention");
			if($gepi_denom_mention=="") {
				$gepi_denom_mention="mention";
			}

			echo $gepi_denom_mention;
		?>" onchange='changement()' /><br />
		</td>
	</tr>

	<tr>
		<td style="font-variant: small-caps;">

		<a name='format_login_resp'></a>
		Mode de génération automatique des logins personnels&nbsp;:</td>
	<td>
		<?php
			$default_login_gen_type=getSettingValue('mode_generation_login');
			if(($default_login_gen_type=='')||(!check_format_login($default_login_gen_type))) {$default_login_gen_type="nnnnnnnp";}
			//echo champs_select_choix_format_login('mode_generation_login', "namef8");
			echo champ_input_choix_format_login('mode_generation_login', $default_login_gen_type);
		?>
	<!--select name='mode_generation_login' onchange='changement()'>
			<option value='name8'<?php if (getSettingValue("mode_generation_login")=='name8') echo " SELECTED"; ?>> nom (tronqué à 8 caractères)</option>
			<option value='fname8'<?php if (getSettingValue("mode_generation_login")=='fname8') echo " SELECTED"; ?>> pnom (tronqué à 8 caractères)</option>
			<option value='fname19'<?php if (getSettingValue("mode_generation_login")=='fname19') echo " SELECTED"; ?>> pnom (tronqué à 19 caractères)</option>
			<option value='firstdotname'<?php if (getSettingValue("mode_generation_login")=='firstdotname') echo " SELECTED"; ?>> prenom.nom (tronqué à 30 caractères)</option>
			<option value='firstdotname19'<?php if (getSettingValue("mode_generation_login")=='firstdotname19') echo " SELECTED"; ?>> prenom.nom (tronqué à 19 caractères)</option>
			<option value='namef8'<?php if (getSettingValue("mode_generation_login")=='namef8') echo " SELECTED"; ?>> nomp (tronqué à 8 caractères)</option>
	</select-->
	</td>
	</tr>

	<tr>
		<td style="font-variant: small-caps;">
			<a name='format_login_resp'></a>
			Mode de génération automatique des logins élèves&nbsp;:
		</td>
		<td>
			<?php
				$default_login_gen_type=getSettingValue('mode_generation_login_eleve');
				if(($default_login_gen_type=='')||(!check_format_login($default_login_gen_type))) {$default_login_gen_type="nnnnnnnnn_p";}
				//echo champs_select_choix_format_login('mode_generation_login_eleve', $mode_generation_login_eleve);
				echo champ_input_choix_format_login('mode_generation_login_eleve', $default_login_gen_type);
			?>
		</td>
	</tr>

	<tr>
		<td style="font-variant: small-caps;">
			<a name='format_login_resp'></a>
			Mode de génération automatique des logins responsables&nbsp;:
		</td>
		<td>
			<?php
				$default_login_gen_type=getSettingValue('mode_generation_login_responsable');
				if(($default_login_gen_type=='')||(!check_format_login($default_login_gen_type))) {$default_login_gen_type="nnnnnnnnnnnnnnnnnnnn";}
				//echo champs_select_choix_format_login('mode_generation_login_responsable', $default_login_gen_type);
				echo champ_input_choix_format_login('mode_generation_login_responsable', $default_login_gen_type);
			?>
		</td>
	</tr>


	<tr>
		<td style="font-variant: small-caps;" valign='top'>
		Mode de génération des mots de passe :<br />(<i style='font-size:small;'>Jeu de caractères à utiliser en plus des caractères numériques</i>)</td>
	<td valign='top'>
		<input type="radio" name="mode_generation_pwd_majmin" id="mode_generation_pwd_majmin_y" value="y" <?php if((getSettingValue("mode_generation_pwd_majmin")=="y")||(getSettingValue("mode_generation_pwd_majmin")=="")) {echo 'checked';} ?> onchange='changement()' /> <label for='mode_generation_pwd_majmin_y' style='cursor: pointer;'>Majuscules et minuscules</label><br />
		<input type="radio" name="mode_generation_pwd_majmin" id="mode_generation_pwd_majmin_n" value="n" <?php if(getSettingValue("mode_generation_pwd_majmin")=="n"){echo 'checked';} ?> onchange='changement()' /> <label for='mode_generation_pwd_majmin_n' style='cursor: pointer;'>Minuscules seulement</label><br />

		<table border='0' summary='Pass'>
		<tr>
		<td valign='top'>
		<input type="checkbox" name="mode_generation_pwd_excl" id="mode_generation_pwd_excl" value="y" <?php if(getSettingValue("mode_generation_pwd_excl")=="y") {echo 'checked';} ?> onchange='changement()' />
		</td>
		<td valign='top'> <label for='mode_generation_pwd_excl' style='cursor: pointer;'>Exclure les caractères prêtant à confusion (<i>i, 1, l, I, 0, O, o</i>)</label><br />
		</td>
		</tr>
		</table>
	</td>
	</tr>




	<tr>
	<td style="font-variant: small-caps;" valign='top'>
		<a name='mode_email_resp'></a>
		<!--Mode de mise à jour des emails responsables et élèves :<br />(<i style='font-size:small;'>Les élèves et responsables peuvent avoir un email dans deux tables s'ils disposent d'un compte utilisateur ('eleves' et 'utilisateurs' pour les premiers, 'resp_pers' et 'utilisateurs' pour les seconds)<br />Ces email peuvent donc se trouver non synchronisés entre les tables</i>)-->
		Mode de mise à jour des emails responsables :<br />(<i style='font-size:small;'>Les responsables peuvent avoir un email dans deux tables s'ils disposent d'un compte utilisateur ('resp_pers' et 'utilisateurs')<br />Ces email peuvent donc se trouver non synchronisés entre les tables</i>)
	</td>
	<td valign='top'>
		<input type="radio" name="mode_email_resp" id="mode_email_resp_sconet" value="sconet" <?php if((getSettingValue("mode_email_resp")=="sconet")||(getSettingValue("mode_email_resp")=="")) {echo 'checked';} ?> onchange='changement()' /> <label for='mode_email_resp_sconet' style='cursor: pointer;'>Mise à jour de l'email via Sconet uniquement</label><br />
		<input type="radio" name="mode_email_resp" id="mode_email_resp_mon_compte" value="mon_compte" <?php if(getSettingValue("mode_email_resp")=="mon_compte"){echo 'checked';} ?> onchange='changement()' /> <label for='mode_email_resp_mon_compte' style='cursor: pointer;'>Mise à jour de l'email depuis Gérer mon compte uniquement<br />&nbsp;&nbsp;&nbsp;&nbsp;(<i>modifications dans Sconet non prises en compte</i>)<br />&nbsp;&nbsp;&nbsp;&nbsp;(<i>sauf sso, voir dans ce cas [<a href='options_connect.php#cas_attribut_email'>Options de connexion</a>]</i>)</label><br />
		<!--
		<input type="radio" name="mode_email_resp" id="mode_email_resp_sso" value="sso" <?php if(getSettingValue("mode_email_resp")=="sso"){echo 'checked';} ?> onchange='changement()' /> <label for='mode_email_resp_sso' style='cursor: pointer;'>Mise à jour de l'email via SSO (<i>???</i>)</label><br />
		-->
	</td>
	</tr>


	<tr>
	<td style="font-variant: small-caps;" valign='top'>
		<a name='mode_email_ele'></a>
		Mode de mise à jour des emails élèves :<br />(<i style='font-size:small;'>Les élèves peuvent avoir un email dans deux tables s'ils disposent d'un compte utilisateur ('eleves' et 'utilisateurs')<br />Ces email peuvent donc se trouver non synchronisés entre les tables</i>)
	</td>
	<td valign='top'>
		<input type="radio" name="mode_email_ele" id="mode_email_ele_sconet" value="sconet" <?php if((getSettingValue("mode_email_ele")=="sconet")||(getSettingValue("mode_email_ele")=="")) {echo 'checked';} ?> onchange='changement()' /> <label for='mode_email_ele_sconet' style='cursor: pointer;'>Mise à jour de l'email via Sconet uniquement</label><br />
		<input type="radio" name="mode_email_ele" id="mode_email_ele_mon_compte" value="mon_compte" <?php if(getSettingValue("mode_email_ele")=="mon_compte"){echo 'checked';} ?> onchange='changement()' /> <label for='mode_email_ele_mon_compte' style='cursor: pointer;'>Mise à jour de l'email depuis Gérer mon compte uniquement<br />&nbsp;&nbsp;&nbsp;&nbsp;(<i>modifications dans Sconet non prises en compte</i>)<br />&nbsp;&nbsp;&nbsp;&nbsp;(<i>sauf sso, voir dans ce cas [<a href='options_connect.php#cas_attribut_email'>Options de connexion</a>]</i>)</label><br />
		<!--
		<input type="radio" name="mode_email_ele" id="mode_email_ele_sso" value="sso" <?php if(getSettingValue("mode_email_ele")=="sso"){echo 'checked';} ?> onchange='changement()' /> <label for='mode_email_ele_sso' style='cursor: pointer;'>Mise à jour de l'email via SSO (<i>???</i>)</label><br />
		-->
	</td>
	</tr>


	<tr>
		<td style="font-variant: small-caps;" valign='top'>
		Type de bulletins par défaut&nbsp;:
		</td>
		<td>
		<input type="radio" id='type_bulletin_par_defaut_pdf' name="type_bulletin_par_defaut" value="pdf"
		<?php
			if(getSettingValue("type_bulletin_par_defaut")=='pdf') {echo " checked";}
		?>
		onchange='changement()' /><label for='type_bulletin_par_defaut_pdf'>&nbsp;PDF</label><br />
		<input type="radio" id='type_bulletin_par_defaut_html' name="type_bulletin_par_defaut" value="html"
		<?php
			if(getSettingValue("type_bulletin_par_defaut")!='pdf') {echo " checked";}
		?>
		onchange='changement()' /><label for='type_bulletin_par_defaut_html'>&nbsp;HTML</label>
		</td>
	</tr>


	<tr>
		<td style="font-variant: small-caps;">
		Feuille de style à utiliser :</td>
	<td>
	<select name='gepi_stylesheet' onchange='changement()'>
			<option value='style'<?php if (getSettingValue("gepi_stylesheet")=='style') echo " SELECTED"; ?>> Nouveau design</option>
			<option value='style_old'<?php if (getSettingValue("gepi_stylesheet")=='style_old') echo " SELECTED"; ?>> Design proche des anciennes versions (1.4.*)</option>
	</select>
	</td>
	</tr>
	<?php
/*
		echo "<tr>\n";
		if(file_exists("../lib/ss_zip.class.php")){
			echo "<td style='font-variant: small-caps;'>Permettre l'export des carnets de notes au format ODS :<br />(<i>si les professeurs ne font pas le ménage après génération des exports,<br />ces fichiers peuvent prendre de la place sur le serveur</i>)</td>\n";
			echo "<td><input type='checkbox' name='export_cn_ods' value='y'";
			if(getSettingValue('export_cn_ods')=='y'){
				echo ' checked';
			}
			echo " />";
			echo "</td>\n";
		}
		else{
			echo "<td style='font-variant: small-caps;'>En mettant en place la bibliothèque 'ss_zip_.class.php' dans le dossier '/lib/', vous pouvez générer des fichiers tableur ODS pour permettre des saisies hors ligne, la conservation de données,...<br />Voir <a href='http://smiledsoft.com/demos/phpzip/' style=''>http://smiledsoft.com/demos/phpzip/</a><br />Une version limitée est disponible gratuitement.</td>\n";
			echo "<td>&nbsp;</td>\n";

			// Comme la bibliothèque n'est pas présente, on force la valeur à 'n':
			$svg_param=saveSetting("export_cn_ods", 'n');
		}
		echo "</tr>\n";
*/
	?>
	<?php
		echo "<tr>\n";
		if(file_exists("../lib/pclzip.lib.php")){
			echo "<td style='font-variant: small-caps;'>Taille maximale extraite des fichiers dézippés:<br />
(<i style='font-size:small;'>Un fichier dézippé peut prendre énormément de place.<br />
Par prudence, il convient de fixer une limite à la taille d'un fichier extrait.<br />
En mettant zéro, vous ne fixez aucune limite.<br />
En mettant une valeur négative, vous désactivez le désarchivage</i>)</td>\n";
			echo "<td valign='top'><input type='text' name='unzipped_max_filesize' id='unzipped_max_filesize' value='";
			$unzipped_max_filesize=getSettingValue('unzipped_max_filesize');
			if($unzipped_max_filesize==""){
				echo '10';
			}
			else {
				echo $unzipped_max_filesize;
			}
			echo "' size='3' onchange='changement()' onkeydown=\"clavier_2(this.id,event,0,600)\" /> Mo";
			echo "</td>\n";
		}
		else{
			echo "<td style='font-variant: small-caps;'>En mettant en place la bibliothèque 'pclzip.lib.php' dans le dossier '/lib/', vous pouvez envoyer des fichiers Zippés vers le serveur.<br />Voir <a href='http://www.phpconcept.net/pclzip/index.php' style=''";
			echo " onclick=\"return confirm_abandon (this, change, '$themessage')\"";
			echo ">http://www.phpconcept.net/pclzip/index.php</a></td>\n";
			echo "<td>&nbsp;</td>\n";
		}
		echo "</tr>\n";
	?>

	<!--tr>
		<td style="font-variant: small-caps;">
		<a name='delais_apres_cloture'></a>
		Nombre de jours avant déverrouillage de l'accès aux appréciations des bulletins pour les responsables et les élèves une fois la période close&nbsp;:<br />
		<div style='font-variant: normal; font-style: italic; font-size: small;'>Sous réserve:<br />
		<ul>
			<li style='font-variant: normal; font-style: italic; font-size: small;'>de créer des comptes pour les responsables et élèves,</li>
			<li style='font-variant: normal; font-style: italic; font-size: small;'>d'autoriser l'accès aux bulletins simplifiés ou aux graphes dans <a href='droits_acces.php'<?php
			//echo " onclick=\"return confirm_abandon (this, change, '$themessage')\"";
			?>>Droits d'accès</a></li>
			<li style='font-variant: normal; font-style: italic; font-size: small;'>d'opter pour le mode de déverrouillage automatique sur le critère "période close".</li>
		</ul>
		</div>
		</td>
		<td valign='top'>
			<?php
			/*
			$delais_apres_cloture=getSettingValue("delais_apres_cloture");
			if($delais_apres_cloture=="") {$delais_apres_cloture=0;}
			echo "<input type='text' name='delais_apres_cloture' size='2' value='$delais_apres_cloture' onchange='changement()' />\n";
			*/
			?>
		</td>
	</tr-->



	<tr>
		<td style="font-variant: small-caps; vertical-align:top;">
		<a name='bul_rel_nom_matieres'></a>
		Pour la colonne matière/enseignement dans les bulletins et relevés de notes, utiliser&nbsp;:
		</td>
		<td valign='top'>

			<?php
			$bul_rel_nom_matieres=getSettingValue("bul_rel_nom_matieres");
			if($bul_rel_nom_matieres=="") {$bul_rel_nom_matieres="nom_complet_matiere";}

			echo "<input type='radio' name='bul_rel_nom_matieres' id='bul_rel_nom_matieres_nom_complet_matiere' value='nom_complet_matiere'";
			if($bul_rel_nom_matieres=='nom_complet_matiere') {echo " checked";}
			echo " onchange='changement()' />\n";
			echo "<label for='bul_rel_nom_matieres_nom_complet_matiere' style='cursor: pointer'> le nom complet de matière</label>\n";
			echo "<br />\n";

			echo "<input type='radio' name='bul_rel_nom_matieres' id='bul_rel_nom_matieres_nom_groupe' value='nom_groupe'";
			if($bul_rel_nom_matieres=='nom_groupe') {echo " checked";}
			echo " onchange='changement()' />";
			echo "<label for='bul_rel_nom_matieres_nom_groupe' style='cursor: pointer'> le nom (court) du groupe</label>\n";
			echo "<br />\n";

			echo "<input type='radio' name='bul_rel_nom_matieres' id='bul_rel_nom_matieres_description_groupe' value='description_groupe'";
			if($bul_rel_nom_matieres=='description_groupe') {echo " checked";}
			echo " onchange='changement()' />";
			echo "<label for='bul_rel_nom_matieres_description_groupe' style='cursor: pointer'> la description du groupe</label>\n";
			?>
		</td>
	</tr>



	<tr>
		<td style="font-variant: small-caps;">
		<a name='mode_ouverture_acces_appreciations'></a>
		<a name='delais_apres_cloture'></a>
		Mode d'accès aux bulletins et résultats graphiques, pour les élèves et leurs
responsables&nbsp;:<br />
		<div style='font-variant: normal; font-style: italic; font-size: small;'>Sous réserve:<br />
		<ul>
			<li style='font-variant: normal; font-style: italic; font-size: small;'>de créer des comptes pour les responsables et élèves,</li>
			<li style='font-variant: normal; font-style: italic; font-size: small;'>d'autoriser l'accès aux bulletins simplifiés ou aux graphes dans <a href='droits_acces.php#bull_simp_ele'<?php
			echo " onclick=\"return confirm_abandon (this, change, '$themessage')\"";
			?>>Droits d'accès</a></li>
		</ul>
		</div>
		</td>
		<td valign='top'>
			<?php
			$acces_app_ele_resp=getSettingValue("acces_app_ele_resp");
			if($acces_app_ele_resp=="") {$acces_app_ele_resp='manuel';}

			echo "<input type='radio' name='acces_app_ele_resp' id='acces_app_ele_resp_manuel' value='manuel' onchange='changement()' ";
			if($acces_app_ele_resp=='manuel') {echo "checked ";}
			echo "/><label for='acces_app_ele_resp_manuel'>manuel (<i>ouvert par la scolarité, classe par classe</i>)</label><br />\n";

			echo "<input type='radio' name='acces_app_ele_resp' id='acces_app_ele_resp_date' value='date' onchange='changement()' ";
			if($acces_app_ele_resp=='date') {echo "checked ";}
			echo "/><label for='acces_app_ele_resp_date'>à une date choisie (<i>par la scolarité</i>)</label><br />\n";

			$delais_apres_cloture=getSettingValue("delais_apres_cloture");
			if($delais_apres_cloture=="") {$delais_apres_cloture=0;}

			echo "<input type='radio' name='acces_app_ele_resp' id='acces_app_ele_resp_periode_close' value='periode_close' onchange='changement()' onkeydown='clavier_2(this.id,event,1,600)' ";
			if($acces_app_ele_resp=='periode_close') {echo "checked ";}
			echo "/><label for='acces_app_ele_resp_periode_close'> <input type='text' name='delais_apres_cloture' value='$delais_apres_cloture' size='1' onchange='changement()' /> jours après la clôture de la période</label>\n";
			?>
		</td>
	</tr>



	<tr>
		<td style="font-variant: small-caps; vertical-align:top;">
		<a name='avis_conseil_classe_a_la_mano'></a>
		Les avis du conseil sont remplis&nbsp;:
		</td>
		<td valign='top'>

			<?php
			$avis_conseil_classe_a_la_mano=getSettingValue("avis_conseil_classe_a_la_mano");
			if($avis_conseil_classe_a_la_mano=="") {$avis_conseil_classe_a_la_mano="n";}

			echo "<input type='radio' name='avis_conseil_classe_a_la_mano' id='avis_conseil_classe_saisis' value='n'";
			if($avis_conseil_classe_a_la_mano=='n') {echo " checked";}
			echo " onchange='changement()' />\n";
			echo "<label for='avis_conseil_classe_saisis' style='cursor: pointer'> avant l'impression des bulletins</label>\n";
			echo "<br />\n";
			echo "<input type='radio' name='avis_conseil_classe_a_la_mano' id='avis_conseil_classe_a_la_mano' value='y'";
			if($avis_conseil_classe_a_la_mano=='y') {echo " checked";}
			echo " onchange='changement()' />";
			echo "<label for='avis_conseil_classe_a_la_mano' style='cursor: pointer'> à la main sur les bulletins imprimés</label>\n";
			?>
		</td>
	</tr>

	<tr>
		<td style="font-variant: small-caps;">
		<a name='ancre_ele_lieu_naissance'></a>
		<label for='ele_lieu_naissance' style='cursor: pointer'>Faire apparaitre les lieux de naissance des élèves&nbsp;:</label><br />
		<div style='font-variant: normal; font-style: italic; font-size: small;'>
			Conditionné par l'utilisation des 'code_commune_insee' importés depuis Sconet et par l'import des correspondances 'code_commune_insee/commune' dans la table 'communes' depuis <a href='../eleves/import_communes.php' <?php
			echo " onclick=\"return confirm_abandon (this, change, '$themessage')\"";
			?>>Import des communes</a>.<br />
		</div>
		</td>
		<td valign='top'>
			<?php
			$ele_lieu_naissance=getSettingValue("ele_lieu_naissance");
			if($ele_lieu_naissance=="") {$ele_lieu_naissance="no";}
			echo "<input type='checkbox' name='ele_lieu_naissance' id='ele_lieu_naissance' value='y'";
			if($ele_lieu_naissance=='y') {echo " checked";}
			echo " onchange='changement()' />\n";
			?>
		</td>
	</tr>

	<tr>
		<td style="font-variant: small-caps;">
		<a name='ancre_exp_imp_chgt_etab'></a>
		<label for='exp_imp_chgt_etab' style='cursor: pointer'>Permettre l'export/import des bulletins d'élèves au format CSV&nbsp;:</label><br />
		<div style='font-variant: normal; font-style: italic; font-size: small;'>
			Le fichier peut être généré pour un élève qui quitte l'établissement en cours d'année.<br />
			L'établissement qui reçoit l'élève peut utiliser ce fichier pour importer les bulletins.<br />
		</div>
		</td>
		<td valign='top'>
			<?php
			$exp_imp_chgt_etab=getSettingValue("exp_imp_chgt_etab");
			if($exp_imp_chgt_etab=="") {$exp_imp_chgt_etab="no";}
			echo "<input type='checkbox' name='exp_imp_chgt_etab' id='exp_imp_chgt_etab' value='yes'";
			if($exp_imp_chgt_etab=='yes') {echo " checked";}
			echo " onchange='changement()' />\n";
			?>
		</td>
	</tr>


	<tr>
		<td style="font-variant: small-caps;">
		N° d'enregistrement à la CNIL : <br />
		<span class='small'>Conformément à l'article 16 de la loi 78-17 du 6 janvier 1978, dite loi informatique et liberté,
		cette installation de GEPI doit faire l'objet d'une déclaration de traitement automatisé d'informations nominatives auprès
		de la CNIL. Si ce n'est pas encore le cas, laissez libre le champ ci-contre</span>
		</td>
		<td><input type="text" name="num_enregistrement_cnil" size="20" value="<?php echo(getSettingValue("num_enregistrement_cnil")); ?>" onchange='changement()' />
		</td>
	</tr>
</table>
<input type="hidden" name="is_posted" value="1" />
<center>
	<input class="confirm_button grey styled_button" type="button" id="button_form_1" name = "OK" value="Enregistrer" style="font-variant: small-caps; display:none;" onclick="test_puis_submit()" />
	<noscript>
		<input type="submit" name = "OK" value="Enregistrer" style="font-variant: small-caps;" />
	</noscript>
</center>
</form>

<?php
	echo "<script type='text/javascript'>
	document.getElementById('button_form_1').style.display='';\n";
	echo insere_js_check_format_login('test_format_login', 'n');

	echo "
	function test_puis_submit() {
		if(!test_format_login(document.getElementById('mode_generation_login').value)) {
			alert('Le format de login des personnels est invalide');
		}
		else {
			if(!test_format_login(document.getElementById('mode_generation_login_eleve').value)) {
				alert('Le format de login des élèves est invalide');
			}
			else {
				if(!test_format_login(document.getElementById('mode_generation_login_responsable').value)) {
					alert('Le format de login des responsables est invalide');
				}
				else {
					document.form1.submit();
				}
			}
		}
	}
</script>\n";

?>

<hr />
<form enctype="multipart/form-data" action="param_gen.php" method="post" name="form2" style="width: 100%;">
<?php
echo add_token_field();
?>
<table border='0' cellpadding="5" cellspacing="5" summary='Logo'>
<?php
echo "<tr><td colspan=2 style=\"font-variant: small-caps;\"><b>Logo de l'établissement : </b></td></tr>\n";
echo "<tr><td colspan=2>Le logo est visible sur les bulletins officiels, ainsi que sur la page d'accueil publique des cahiers de texte</td></tr>\n";
echo "<tr><td>Modifier le Logo (png, jpg et gif uniquement) : ";
echo "<input type=\"file\" name=\"doc_file\" onchange='changement()' />\n";
echo "<input type=\"submit\" name=\"valid_logo\" value=\"Enregistrer\" /><br />\n";
echo "Supprimer le logo : <input type=\"submit\" name=\"sup_logo\" value=\"Supprimer le logo\" /></td>\n";


$nom_fic_logo = getSettingValue("logo_etab");

$nom_fic_logo_c = "../images/".$nom_fic_logo;
if (($nom_fic_logo != '') and (file_exists($nom_fic_logo_c))) {
echo "<td><b>Logo actuel : </b><br /><img src=\"".$nom_fic_logo_c."\" border='0' alt=\"logo\" /></td>\n";
} else {
echo "<td><b><i>Pas de logo actuellement</i></b></td>\n";
}
echo "</tr></table></form>\n";
?>

<p><i>Remarques&nbsp;</i> Les transparences sur les images PNG, GIF ne permettent pas une impression PDF (<i>canal alpha non supporté par fpdf</i>).<br />
Il a aussi été signalé que les JPEG progressifs/entrelacés peuvent perturber la génération de PDF.</p>

<hr />
<form enctype="multipart/form-data" action="param_gen.php" method="post" name="form3" style="width: 100%;">
<?php
echo add_token_field();
?>
<table border='0' cellpadding="5" cellspacing="5" summary='Pmv'>
	<tr>
		<td style="font-variant: small-caps;">
		Tester la présence du module phpMyVisite (<i>pmv.php</i>) :</td>
	<td>
		<input type="radio" name="gepi_pmv" id="gepi_pmv_y" value="y" <?php if(getSettingValue("gepi_pmv")!="n"){echo 'checked';} ?> onchange='changement()' /><label for='gepi_pmv_y' style='cursor: pointer;'> Oui</label><br />
		<input type="radio" name="gepi_pmv" id="gepi_pmv_n" value="n" <?php if(getSettingValue("gepi_pmv")=="n"){echo 'checked';} ?> onchange='changement()' /><label for='gepi_pmv_n' style='cursor: pointer;'> Non</label><br />
	</td>
	</tr>
</table>

<input type="hidden" name="is_posted" value="1" />
<center><input type="submit" name = "OK" value="Enregistrer" style="font-variant: small-caps;" /></center>

<table summary='Remarque'><tr><td valign='top'><i>Remarque:</i></td><td>Il arrive que ce test de présence provoque un affichage d'erreur (<i>à propos de pmv.php</i>).<br />
Dans ce cas, désactivez simplement le test.</td></tr></table>
</form>
<p><br /></p>

<?php
require("../lib/footer.inc.php");
?>
