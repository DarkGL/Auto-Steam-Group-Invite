<?php

class Barrett {
	
	private $r2 , $q3 , $mu , $m;
	
	public function __construct( $m ){
		$this -> r2 = nbi();
  		$this -> q3 = nbi();
  		getOne() -> dlShiftTo( 2 * $m -> t , $this -> r2 );
  		$this -> mu = $this -> r2 -> divide( $m );
  		$this -> m = $m;
	}
	
	public function convert( $x ) {
  		if( $x -> s < 0 || $x -> t > 2 * $this -> m -> t) return $x -> mod( $this -> m );
  		else if( $x.compareTo( $this -> m ) < 0) return $x;
  		else { $r = nbi(); $x -> copyTo( $r ); $this -> reduce( $r ); return $r; }
	}
	
	public function revert( $x ) { return $x; }
	
	public function reduce( $x ) {
  		$x -> drShiftTo( $this -> m -> t - 1 , $this -> r2 );
  		if( $x -> t > $this -> m -> t + 1 ) { $x -> t = $this -> m -> t + 1; $x -> clamp(); }
  		$this -> mu -> multiplyUpperTo( $this -> r2, $this -> m -> t + 1 , $this -> q3);
  		$this -> m -> multiplyLowerTo( $this -> q3, $this -> m -> t + 1 , $this -> r2);
  		while( $x -> compareTo( $this -> r2 ) < 0) $x -> dAddOffset( 1 , $this -> m -> t + 1 );
  		$x.subTo( $this -> r2 , $x );
  		while( $x -> compareTo( $this -> m ) >= 0) $x -> subTo( $this -> m , $x );
	}
	
	public function sqrTo( $x , $r ) { $x -> squareTo( $r ); $this -> reduce( $r ); }
	
	public function mulTo( $x , $y , $r ) { $x -> multiplyTo( $y , $r ); $this -> reduce( $r ); }
}

?>