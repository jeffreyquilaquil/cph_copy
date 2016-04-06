<?php
if(isset($_POST['submit'])) {
	// Get information from the form
	$table_name = $_POST['table_name'];
	$field_type = $_POST['field_type'];
	$csv_data   = $_POST['csv_data'];
}
?>
<HTML>
<HEAD>
<TITLE>Tab Delimited Text to SQL Converter</TITLE>
<!-- Instructions for this code are at http://goodfeelingplace.com/free-mdb-file-to-mysql-conversion/ -->
</HEAD>
<BODY>
<H2>Tab Delimited Text to SQL Converter</H2>

<!-- Input form begin -->

<FORM NAME="csv2sql" METHOD="POST" ACTION="#">
  Table Name:
	<INPUT TYPE="TEXT" NAME="table_name" VALUE="<? echo $table_name; ?>" SIZE=50>
	<BR>
  Default Field Type:
	<INPUT TYPE="TEXT" NAME="field_type" VALUE="<? echo $field_type; ?>" SIZE=50>
	<BR>
  Input data: 
	<BR>
	<TEXTAREA name="csv_data" ROWS=15 COLS=80><? echo $csv_data; ?></TEXTAREA>
	<BR><BR>
	<INPUT name="submit" type="submit" value="Convert to SQL Queries">
</FORM>

<!-- Input form end -->

<?php

// Parse incoming information if above form was posted
if(isset($_POST['submit'])) {
	echo "<h2>SQL Queries:</h2>";

	// Prepare data for parsing
	$csv_array    = explode("\n",$csv_data);
	$column_names = explode("\t",$csv_array[0]);

	// Generate base query and table creation query
	$create_query = "CREATE TABLE IF NOT EXISTS `$table_name` (";
	$base_query = "INSERT INTO `$table_name` (";
	$first = true;
	foreach($column_names as $column_name) {
		// The first field doesn't need a comma in front of it, the others do
		if(!$first)	{
			$base_query .= ", ";	
			$create_query .= ", ";
		}
		$column_name = trim($column_name);
		$base_query .= "`$column_name`";
		$create_query .= "`$column_name` $field_type";
		$first = false;
	} // end of loop through column names
	$base_query .= ") ";
	$create_query .= ");";
	echo "<b><u>Table Creation Statement:</u></b><br>$create_query<BR><br>";
	echo "<b><u>Data Insert Statements:</u></b><br>";

	// Loop through all CSV data rows and generate separate
	// INSERT queries based on base_query + the row information
	$last_data_row = count($csv_array) - 1;
	for($counter = 1; $counter <= $last_data_row; $counter++) {
		$value_query = "VALUES (";
		$first = true;
		$thisRow = $csv_array[$counter];
		$emptyRowCheck = trim($thisRow);
		if (empty($emptyRowCheck)) continue; //skip empty rows
		else $data_row = explode("\t",$thisRow);
		$value_counter = 0;
		foreach($data_row as $data_value) {
			if(!$first)	$value_query .= ", ";	// the first field doesn't need a comma in front, the others do
			$data_value = trim($data_value);
			// Convert dates to the format that MySQL will understand as a date (yyyy-mm-dd)
			//   A value is considered a date if it starts with at least 1 number, has a / then at least 1 number,
			//   another / then at least 2 more numbers - AND the length of the value is less than 25 characters
			if ((preg_match('@^\d+/\d+/\d+@',$data_value)==1)&&(strlen($data_value)<25)) {
				$data_value_seconds = strtotime($data_value);
				// only do the conversion if the value is a real date
				if ($data_value_seconds>0) $data_value = date('Y-m-d',$data_value_seconds);
			}
			$value_query .= "'$data_value'";
			$first = false;
		} // end of loop through data values in a single input row
		$value_query .= ")";

		// Combine generated insert queries into one string and print it
		$query = $base_query .$value_query .";";
		echo "$query <BR>";
	} // end of loop through data records, or rows in the input textarea
} // end of check for submitted data
?>

