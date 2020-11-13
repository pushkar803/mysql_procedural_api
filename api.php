<?php

$servername = "db_servername";
$username = "db_username";
$password = "db_password";
$dbname = "db_name";

$conn = null;

try {

	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} 
catch(PDOException $e) {
  echo "Error: " . $e->getMessage();
}

$in_json = file_get_contents('php://input');

// Converts it into a PHP object
$in_data = json_decode($in_json);
header('Content-Type: application/json');  // <-- header declaration

if(isset($in_data->{"endpoint"})){
	
	if( $in_data->{"endpoint"} == "get_data_by_procedure" ){

		if(isset($in_data->{"in_procedure"})){

		$json = '{"procedure_name":"firma" ,"procedure_data":{"in_Country":"Romania"}}';

		//$json = json_decode($json);
		//$json = json_encode($in_data->{"in_procedure"});
		//$json = json_decode($json);
		//echo(json_encode($in_data->{"in_procedure"}));

		echo get_by_procedure($in_data->{"in_procedure"});
		//echo get_by_procedure($json);

		}
		else{
			echo json_encode(array("status"=>"400"));
			exit();
		}
	}
	else{
		echo json_encode(array("status"=>"400"));
	}
}
else{
	echo json_encode(array("status"=>"400"));
}

function get_by_procedure($in_data){

	global $conn;
	$procedure_name = $in_data->{'procedure_name'};
	$procedure_data = $in_data->{'procedure_data'};
	//var_dump($procedure_name);
	
	$stmt = $conn->prepare("SELECT  PARAMETER_NAME FROM information_schema.parameters WHERE SPECIFIC_NAME = :procedure_name");
	$stmt->bindParam(':procedure_name', $procedure_name);

	$stmt->execute();

	$op_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	//print_r($op_data);
	$proc_params = [];
	foreach($op_data as $row) {
		if(isset($procedure_data->{$row['PARAMETER_NAME']})){
			//echo($procedure_data->{$row['PARAMETER_NAME']});
			array_push($proc_params,"@".$row['PARAMETER_NAME'].":='".$procedure_data->{$row['PARAMETER_NAME']}."'");
		}
		else{
			array_push($proc_params,"@".$row['PARAMETER_NAME'].":='undefined'");
		}
	}
	
	$proc_params = implode(",", $proc_params);
	$query = "call ".$procedure_name."(".$proc_params.")";
	//var_dump($query);
	
	$stmt = $conn->prepare($query);
	$stmt->execute();

	$op_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	//var_dump($op_data);
	$filter_arr = array("Status", "ProcedureName", "Message", "ProcedureID");
	//var_dump($op_data[0]['Status']);
	
	$Status = isset($op_data[0]['Status']) ? $op_data[0]['Status'] : '401';
	$ProcedureName = isset($op_data[0]['ProcedureName']) ? $op_data[0]['ProcedureName'] : 'NA';
	$Message = isset($op_data[0]['Message']) ? $op_data[0]['Message'] : 'NA';
	$ProcedureID = isset($op_data[0]['ProcedureID']) ? $op_data[0]['ProcedureID'] : 'NA';
	
	$temp_op = [];
	foreach($op_data as $row) {
		$temp_row = [];
		foreach($row as $col_name => $col_val ) {
			if(!in_array($col_name,$filter_arr))
				$temp_row[$col_name] = $col_val;
		}
		array_push($temp_op,$temp_row);
	}
	//var_dump($temp_op);
	
	$final_op = [];
	$final_op['Status'] = $Status;
	$final_op['ProcedureName'] = $ProcedureName;
	$final_op['Message'] = $Message;
	$final_op['ProcedureID'] = $ProcedureID;
	$final_op['Result'] = $temp_op;
	return json_encode($final_op);

}

$conn = null;

?>