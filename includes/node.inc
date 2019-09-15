<?php


function node_load_multiple($nids = null)
{
	$nid = array_pop( $nids );
	$query = 'SELECT * FROM node WHERE nid=:1';
	$results = db_query( $query, $nid );
	return $results->fetchall_assoc();
}

/**
 * @return - FALSE if the user doesn't have access to this node, otherwise TRUE
 *
 */
function node_access() {
	global $user, $path;// however, we'll mostly refer to $node->querystring ($node->q)
	$query = new DBQuery(
		$params = array(
			"type"=>"select",
			"tablenames"=>array(
				0	=>array(
					"name"=>"Documents",
					"op"=>"",
					"fields"=> array()
					)
			),
			"schema"=>array(
				"docs"
			),
			/*"where"=>array(
				"Path REGEXP '^$path'",
				"OR LOCATE(Path,'$path'>0"
			)*/
			"where"=>array(
				"(Path REGEXP '^{$path}' AND Path<>'')",
				"OR (LOCATE(Path,'$path')>0 AND Path<>'')"
			)
		)
	);//DBQuery
	$results = $query->exec();
	if( $query->getNumRows() < 1 ) return TRUE; // there is no access restriction in the node table
	// tail( $query->getQuery() );
	$access = $results[0]['Security'];
	if( !in_array( $access, $user->roles) ) return FALSE;
	else return TRUE;
}