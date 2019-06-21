<?php 

//$serverName = "serverName\sqlexpress"; //serverName\instanceName
$serverName = "172.22.1.22"; //serverName\instanceName
$connectionInfo = array( "Database"=>"ARCO", "UID"=>"usuario", "PWD"=>"usuac");
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {
     echo "Conexión establecida.<br />";
}else{
     echo "Conexión no se pudo establecer.<br />";
     die( print_r( sqlsrv_errors(), true));
}

if( $conn === false ) {
   echo "Could not connect.\n";
   die( print_r( sqlsrv_errors(), true));
}
/* Set up and execute the query. */
$sql = "SELECT     MASTER_CICLOS_CORREGIDOS.SERIECICLO, MASTER_CICLOS.CICLO, MASTER_CICLOS_CORREGIDOS.HORA_INICIO, 
                      MASTER_CICLOS_CORREGIDOS.HORA_FIN
FROM         MASTER_CICLOS_CORREGIDOS INNER JOIN
                      MASTER_CICLOS ON MASTER_CICLOS_CORREGIDOS.ID_CICLO = MASTER_CICLOS.ID_CICLO
where LEN(MASTER_CICLOS_CORREGIDOS.SERIECICLO) = 10
ORDER BY MASTER_CICLOS_CORREGIDOS.HORA_INICIO DESC";
$stmt = sqlsrv_query($conn, $sql);

// This is where the data will be organized.
// It's better to always initialize the array variables before putting data in them
$data = array();

// Get the rows one by one
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Extract the activity name; we want to group the rows by it
    $name = $row['ACTIVITY_NAME'];
    $group = '';
    $sdate = '';
    $edate = '';
    $completed = '';
    $total = '';
    $perc = '';

    // Check if this activity was encountered before
    if (! isset($data[$name])) {
        // No, this is the first time; we will make room for it, first
        $data[$name] = array(
            // Remember the name
            'ACTIVITY_NAME' => $name,
            'MAINTENANCE_GROUP' => $group,
            'START_DATE' => $sdate,
            'END_DATE' => $edate,
            'COMPLETED' => $completed,
            'TOTAL_CLUSTERS' => $total,
            'COMPLETE_PERC' => $perc,
            // No children yet
            'children' => array(),
        );
    }
    // Put the row into the list of children for this activity
    $data[$name]['children'][] = $row;
}

// Here, the entries in $data are indexed by the values they also have in                  'ACTIVITY_NAME'
// If you want them numerically indexed, all you have to do is:
$data = array_values($data);
echo json_encode(array('data' => $data));
//echo json_encode($data);
?>
