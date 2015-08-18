<?php

class NullExp {
	
	public function convert( $x ) { return $x; }
	
	public function revert( $x ){ return $x; }
	
	public function mulTo( $x , $y , $r ) { $x -> multiplyTo( $y , $r ); }
	
	public function sqrTo( $x , $r ) { $x -> squareTo( $r ); }
	
}

?>