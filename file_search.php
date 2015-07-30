<?php
require 'config.php';

$text = $_GET['text'];

$home_dir = '/home/careerph/public_html/';
$output = 'uploads/search_results/'. date('Ymd') . $text . '.json';

$cmd = "sudo ./file_search.sh {$text} {$output}";
exec( $cmd );

$contents = file_get_contents( $output );
$tayo = json_encode( $contents );
unlink( $output );

$lingkod = explode('\n', str_replace('"','', $tayo));

$appID = '';
foreach($lingkod AS $x){
	$xtext = str_replace('\/home\/careerph\/public_html\/uploads\/applicants\/','', $x);
	$ex = explode('\/', $xtext);
	
	if(isset($ex[0])){
		$appID .= $ex[0].',';		
	}
		
}

$querySearch = $db->selectQuery("applicants", 
					"applicants.id, CONCAT(fname, ' ', lname) AS name, email, mnumber, applicants.date_created, isNew, process, processType, processText, processStat, IF(isNew = 0, (SELECT title FROM positions WHERE position=positions.id LIMIT 1), (SELECT title FROM newPositions WHERE position=posID LIMIT 1)) as title", 
					"id IN (".rtrim($appID,",").") ORDER BY date_created DESC", 
					"LEFT JOIN recruitmentProcess ON processID=process");
					
	foreach($querySearch AS $info){
		$nw = '';
		if($info['isNew']==0) $nw = ' [<b>old</b>]';
		echo '<tr>
				<td>'.$info['id'].'</td>
				<td><a href="view_info.php?id='.$info['id'].'">'.$info['name'].'</a></td>
				<td><a href="mailto:'.$info['email'].'">'.$info['email'].'</a></td>
				<td>'.$info['mnumber'].'</td>
				<td>'.$info['title'].$nw.'</td>
				<td>'.date('Y-m-d', strtotime($info['date_created'])).'</td>';
		if(empty($info['processText'])) echo '<td>'.$info['processType'].' <span style="color:green;">[in progress]</span></td>';
		else echo '<td><span style="color:red;">'.$info['processText'].'</span></td>';
		
		if($info['processStat']==0)
			echo 	'<td><br/></td>';
		else
			echo 	'<td><a class="iframe" href="editstatus.php?id='.$info['id'].'"><img src="images/edit_icon.png"/></a></td>';
			
		echo	'</tr>';			
	}

