<?php

class Classic {
	
	private $m;
	
	public function __construct ( $m ){
			
		$this -> m	=	$m;
		
	}
	
	public function convert( $x ){
		if( $x -> s < 0 || $x -> compareTo( $this -> m ) >= 0) return $x -> mod( $this -> m);
  		else return $x;
	}
	
	public function revert( $x ){
		return $x;
	}
	
	public function reduce( $x ){
		$x -> divRemTo( $this -> m , null , $x );
	}
	
	public function mulTo( $x , $y , $r ){
		$x -> multiplyTo( $y , $r ); 
		$this -> reduce( $r );
	}
	
	public function sqrTo( $x , $r ){
		$x -> squareTo( $r ); 
		$this -> reduce( $r );
	} 
}

?>