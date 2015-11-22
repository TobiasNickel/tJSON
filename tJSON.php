<?php
class TJSON{
	private function isAssoc($arr){
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	private $refName='_:';

    // ***************************************
    // stringify Methods
    // ***************************************
	public $paths=[];  //$paths and $objects are two arrays,
	public $objects=[]; // that are organized parrallel for mapping
	private $json='';
	private function stringifyObject(&$o,$path){
        $this->json .= '{';
        $tmp=[];// contains [key, value];
        $first=true;
		$keys = array_keys($o);
		sort($keys);
        for($c=0;$c<count($keys);$c++){
			$i = $keys[$c];
            if($first){ $first=false; }else{ $this->json.=','; }
            $this->json.= json_encode($i).':';
            $this->stringify($o[$i],$path.'.'.$i);
        }
        $this->json.='}';
    }

	private function stringifyArray(&$o,$path){
		$this->json.='[';
		for($i=0; $i<count($o); $i++){
			if($i>0)$this->json.=',';
			$this->stringify($o[$i],$path.'.'.$i);
		}
		$this->json.=']';
	}
    // ***************************************
    // parsing Methods
    // ***************************************
	private $resolved=[];
	/**
	 * resolve the pathFinding for tJSON, in assoc Arrays
	 */
	private function resolveReferences(&$o,&$root){
    $this->resolved[]=$o;
    foreach($o as $i => $v){
			if(is_object($o)){
				if(is_string($o->$i)){
		        if(strpos($o->$i, $this->refName) === 0){
		            $o->$i = $this->getObj($root,substr($o->$i,count($this->refName)+1));//$o[$i]->slice($refName->length+1));
		        }
		    }else{
		      if(!in_array($o->$i,$this->resolved)){
		        $this->resolveReferences($o->$i,$root);
					}
				}
			}else{
		    if(is_string($o[$i])){
		        if(strpos($o[$i], $this->refName) === 0){
		            $o[$i] = $this->getObj($root,substr($o[$i],count($this->refName)+1));//$o[$i]->slice($refName->length+1));
		        }
		    }else{
		      if(!in_array($o[$i],$this->resolved)){
		        $this->resolveReferences($o[$i],$root);
					}
				}
			}
    }
  }

	private function resolveReferencesAssoc(&$o,&$root){}

	private function indexOf(&$o){
		for($i=0;$i<count($this->objects);$i++){
			if($this->objects[$i] === $o){
				return $i;
			}
		}
		return -1;
	}
	/**
	 * get object by path.
	 * @root, the object to search in
	 * @$path {string} the path, seperated by .
	 */
	private function getObj(&$root,$path){
		var_dump($root);
		echo $path;
    $pathParts = explode('.', $path);//$path->split('.');
    $obj = $root;
    $n = '';
    while($n = array_shift($pathParts)){// = array('' => , );$pathParts->shift()){
      if(count($n)){//$n.length
        $obj = $obj[$n];
      }
    }
    return $obj;
  }
	public function stringify($o,$path=null){
		$n=false;
		if($path===null){
			$path =  '';
			$this->paths=[];
			$this->objects=[];
			$this->json='';
			$n=true;
		}
		if(is_string($o) || is_numeric($o)){
			$this->json.=json_encode($o);
		}else if(true){
			//var_dump($o);
			$index = $this->indexOf($o);
			if($index != -1){
				$this->json.=json_encode(
					$this->refName . $this->paths[$index]// . '.' . array_search($o,$this->objects)
				);
			}else{
				$this->paths[] = $path;
				$this->objects[] = $o;
				if(is_array($o) && !$this->isAssoc($o)){
					$this->stringifyArray($o,$path);
				}else{
					$this->stringifyObject($o,$path);
				}
			}
		}
		if($n){
			// clean up some memory
			//$this->paths=[];  $this->objects=[];
		}
		return $this->json;
	}
	public function parse($s, $assoc=false){
		$o=json_decode($s,$assoc);
		$this->resolved=[];
		$this->resolveReferences($o, $o);
		$this->resolved=[];
		return $o;
	}
}

$tJSON = new TJSON();

//echo $tJSON->stringify(['test',1,['value'=>4]]).'<br>';
/*
$a=[];
$b=[];
$a['b']=&$b;
$b['a']=&$a;
echo var_dump($a).'<br>';
echo var_dump($b).'<br>';
var_dump($a['b']['a']['b']['a']['b']['a']['b']['a']);
*/

$a=[];
$b=['a' => [&$a]];
//$c=['b' => &$b];
$a['b'] = &$b;
//$i=3;
//$a['i'] = $i;
//$b['i'] = $i;
//$c['i'] = $i;
//$a['i'] = $a;
//$arr = [$a,$b,$c,$i];
//$c['arr'] = $arr;
//foreach($a as $i => $v){
//echo '<br>'.$i.'<br>';
//}
echo 'stringify A: '.$tJSON->stringify($a)."\n";
//echo 'parse: '.$tJSON->stringify($arr).'<br>';
var_dump($tJSON->parse($tJSON->stringify($a)));//."\n";
// */
echo "\n test is array";
echo "\n";
echo "".is_object(new stdClass());

echo "\n";

?>
