<?php

class Montgomery {
	
	public $m , $mp , $mpl , $mph , $um , $mt2;
	
	public function __construct( $m ){
		$this -> m = $m;
  		$this -> mp = $m -> invDigit();
  		$this -> mpl = $this -> mp & 0x7fff;
  		$this -> mph = $this -> mp >> 15;
  		$this -> um = ( 1 << ( bigInteger::$DB - 15 ) ) - 1;
  		$this -> mt2 = 2*$m -> t;
	}
	
	public function convert( $x ) {
		$r = nbi();
  		$x -> absFunc() -> dlShiftTo( $this -> m -> t , $r );
  		$r -> divRemTo( $this -> m , null , $r );
  		if( $x -> s < 0 && $r -> compareTo( getZero() ) > 0) $this -> m -> subTo( $r , $r );
  		return $r;
	}
	
	public function revert( $x ) {
		$r = nbi();
  		$x -> copyTo( $r );
  		$this -> reduce( $r );
  		return $r;
	}
	
	public function reduce( &$x ) {
  		while( $x -> t <= $this -> mt2){    // pad x so am has enough room later
    		$x -> setData( $x -> t ++ , 0 );
		}
		
  		for( $i = 0; $i < $this -> m -> t; ++$i) {
  			
    		$j = $x -> getData( $i ) & 0x7fff;
			
			$u0 =  ( $j * $this -> mpl + ( ( ( $j * $this -> mph + ( $x -> getData( $i ) >> 15 ) * $this -> mpl ) & $this -> um ) << 15) ) & bigInteger::$DM;

    		$j = $i + $this -> m -> t;
			$x -> addData( $j , $this -> m -> am( 0 , $u0 , $x , $i , 0 , $this -> m -> t) );
			
    		while( $x -> getData( $j ) >= bigInteger::$DV) {
				$x -> subData( $j , bigInteger::$DV );
				$x -> addData( ++$j , 1 ); 
			}
		}
		
  		$x -> clamp();
		
  		$x -> drShiftTo( $this -> m -> t , $x );
		
  		if( $x -> compareTo( $this -> m ) >= 0 ){
  			 $x -> subTo( $this -> m , $x );
		}
		
	}
	
	public function sqrTo( &$x , &$r ) {
		$x -> squareTo( $r );
		$this -> reduce( $r ); 
	}
	
	public function mulTo( $x , $y , $r ) {
		$x -> multiplyTo( $y , $r ); 
		$this -> reduce( $r ); 
	}
}

?>