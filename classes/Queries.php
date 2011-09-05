<?

class Queries{
  public static function uriExist($uri, $e){
  	$q = "SELECT * WHERE{
  	{<$uri> ?p1 ?o1}
  	UNION
  	{?s1 <$uri> ?o2}
  	UNION
  	{?s2 ?p2 <$uri>}
  	}LIMIT 1";
  	
  	$r = $e->query($q);
  	if(sizeof($r['results']['bindings'])>0){
  	  return true;
  	}
  	return false;
  }
  
  public static function getClass($uri, $e){
  	$q = "SELECT DISTINCT ?class WHERE{
  	<$uri> a ?class .
  	} LIMIT 1";
  	$r = $e->query($q);
  	if(sizeof($r['results']['bindings'])>0){
  	  return $r['results']['bindings'][0]['class']['value'];
  	}
  	return NULL;
  }
  
	public static function getMetadata($uri, $format, $e){
		global $conf;
		$q = <<<QUERY
		SELECT uri, doc, format FROM document WHERE 
			(uri = "$uri" AND format = "$format") OR doc = "$uri"
		LIMIT 1
QUERY;
	   $r = $e->query($q);
		if(sizeof($r) > 0){
		 $u = $r[0]['uri'];
		 $p = $r[0]['doc'];
		 $f = $r[0]['format'];
		  return array($u, $p, $f);
		}else{
		  return NULL;
		}
	}
  

	public static function createPage($uri, $contentType, $e){
	 global $conf;
		$ext = 'html';
		$inserts = "";
		foreach($conf['http_accept'] as $extension => $f){
		  $page = $uri.".".$extension;
			foreach($f as $v){
			  if($contentType == $v){
				$returnPage = $uri.".".$extension;
			  }
			  if($inserts != ""){
				$inserts .= "UNION ";
			  }
			  $inserts .= "SELECT '$uri', '$page', '$v' \n";
			  if($v == $contentType){
				$ext = $extension;
			  }
			}
		  }
		  $q = <<<QUERY
		  INSERT INTO document (uri, doc, format) $inserts
QUERY;
	$r = $e->write($q);
	
		return $returnPage;
	}
	
	  public static function processQuery($modelFile, $uri, $endpoint){
    global $conf;
    //Check if files for model and view exist
	
	$query = file_get_contents($modelFile);
	$query = preg_replace("|".$conf['resource']['url_delimiter']."|", "<".$uri.">", $query);
	header('Content-Type: '.$acceptContentType);
	if(preg_match("/describe/i", $query)){
	  $results = $endpoint->query($query, $conf['endpoint']['describe']['output']);
	  require('lib/arc2/ARC2.php');
	  $parser = ARC2::getRDFParser();
	  $parser->parse($conf['basedir'], $results);
	  $triples = $parser->getTriples();
	  $ser;
	  switch ($extension){
	  case 'ttl':
	  	$ser = ARC2::getTurtleSerializer();
	  	break;
	  case 'nt':
	  	$ser = ARC2::getNTriplesSerializer();
	  	break;
	  case 'rdf':
	  	$ser = ARC2::getRDFXMLSerializer();
	  	break;
	  }
	  $doc = $ser->getSerializedTriples($triples);
	  echo $doc;
	  exit(0);
	}
	elseif(preg_match("/select/i", $query)){
	  $results = $endpoint->query($query, $conf['endpoint']['select']['output']);
	  if(sizeof($results['results']['bindings']) == 0){
	  	Utils::send404($uri);
	  }
	  return $results;
	}
  }
}

?>
