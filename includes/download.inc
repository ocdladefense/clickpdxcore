<?php



function is_member_download( $productid=NULL, $memberid=NULL, $i=NULL ) {
	if( !isset($productid) ) throw new Exception('The product for this download was not specified.');
	if( isset($i) ) {			
		$download = new DBQuery(
			array(
				'type' => 'select',
				'tablenames'=>array(
					0	=>array(
						"name"=>"downloads",
						"op"=>"",
						"fields"=> array()
						)
				),
				'where' => array(
					"i={$i} AND",
					"productid={$productid}",
				),
			)
		);
	} else {
		$download = new DBQuery(
			array(
				'type' => 'select',
				'tablenames'=>array(
					0	=>array(
						"name"=>"downloads",
						"op"=>"",
						"fields"=> array()
						)
				),
				'where' => array(
					"memberid={$memberid} AND",
					"productid={$productid}",
				),
			)
		);
	}
		$results = $download->exec();
		if( $download->getNumRows() < 1 ) return FALSE;
		if( empty($results[0]['memberid']) ) return FALSE;
		else return $results[0]['memberid'];

}// is_member_downloads



function is_valid_download( $productid = null, $memberid = null, $i = null ) {
	if( empty($memberid) ) {
		throw new Exception('/includes/downloads.inc, is_valid_download() says: Member id empty.');
	}
	if( empty($productid) ) {
		throw new Exception("UserID: {$memberid} No ProductID given from downlods.inc.is_valid_download");
	}
	
	$download = new DBQuery(
		array(
			'type' => 'select',
			'tablenames'=>array(
				0	=>array(
					"name"=>"downloads",
					"op"=>"",
					"fields"=> array()
					)
			),
			'where' => array(
				"productid={$productid} AND",
				"memberid={$memberid}",
			),
		)
	);
	$results = $download->exec();
	if( $download->getNumRows() < 1 ) {
		throw new Exception("/includes/downloads.inc, is_valid_download(), says User doesn't have a value in the downloads table.");
	}
	return true;
}