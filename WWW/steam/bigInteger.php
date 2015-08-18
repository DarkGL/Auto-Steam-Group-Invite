<?php

include_once( "classic.php" );
include_once( "montgomery.php" );
include_once( "nullExp.php" );
include_once( "barrett.php" );

class bigInteger {
	
	private static $dBits	=	28;
	private static $BI_FP	=	52;
	private static $BI_RM 	= "0123456789abcdefghijklmnopqrstuvwxyz";
	private static $BI_RC 	= array();
	
	public static $DB , $DM , $DV;
	
	private static $FV , $F1 , $F2;
	
	private $iData;
	
	public $t;
	public $s;
	
	public function __construct( $a = NULL , $b = NULL , $c = NULL ){
		
		$this -> t	=	0;
		$this -> s	=	0;
		
		$rr = ord( "0" );
		for( $vv = 0; $vv <= 9; ++$vv ) self::$BI_RC[ $rr++ ] = $vv;
		
		$rr = ord( "a" );
		for( $vv = 10; $vv < 36; ++$vv ) self::$BI_RC[ $rr++ ] = $vv;
		
		$rr = ord( "A" );
		for( $vv = 10; $vv < 36; ++$vv ) self::$BI_RC[ $rr++ ] = $vv;
		
		$this -> iData	=	array();
		
		self::$DB		=	self::$dBits;
		self::$DM		=	( ( 1 << self::$dBits ) - 1 );
		self::$DV		=	( 1 << self::$dBits );
		
		self::$FV		=	pow( 2 , self::$BI_FP );
		self::$F1		=	self::$BI_FP - self::$dBits;
		self::$F2		=	2 * self::$dBits - self::$BI_FP;
		
		if( $a != NULL ){
			
			if( gettype( $a ) == "integer" ){
				$this -> fromNumber( $a , $b , $c );
			
			}
			elseif( $b == NULL && gettype( $a ) != "string" ){
				
				$this -> fromString( $a , 256 );
				
			}
			else{
				
				$this -> fromString( $a , $b );
				
			}
		}
	}
	
	public function getData( $i ){
		return $this -> iData[ $i ];
	}
	
	public function setData( $i , $iValue ){
		$this -> iData[ $i ]	=	$iValue;
	}
	
	public function addData( $i , $iAdd ){
		$this -> iData[ $i ]	+=	$iAdd;
	}
	
	public function subData( $i , $iSub ){
		$this -> iData[ $i ]	-=	$iSub;
	}
	
	public function am( $i , $x , $w , $j , $c , $n ) {
		
		$xl = $x & 0x3fff;
		$xh = $x >> 14;
		
		while( --$n >= 0 ) {
			$l = $this -> getData( $i ) & 0x3fff;
			$h = $this -> getData( $i++ ) >> 14;
			$m = $xh * $l + $h * $xl;
			
			$l = $xl * $l+( ( $m & 0x3fff ) << 14 ) + $w -> getData( $j ) + $c;
			$c = ( $l >> 28 ) + ( $m >> 14) + $xh * $h;
			$w -> setData( $j++ , $l & 0xfffffff );
  		}
		
  		return $c;
	}
	
	private static function int2char( $n ){
		
		return self::$BI_RM[ $n ];
		
	}
	
	private function intAt( $s ,$i ) {
		$c = self::$BI_RC[ ord( $s[ $i ] ) ];
	  	return ( $c === null) ? -1 : $c;
	}
	
	public function copyTo( &$r ) {
		
		for( $i = $this -> t - 1; $i >= 0; --$i) $r -> setData( $i , $this -> getData( $i ) );
		
		$r -> t = $this -> t;
		$r -> s = $this -> s;
		
	}
	
	public function fromInt( $x ) {
  		$this -> t = 1;
  		$this -> s = ( $x < 0)?-1:0;
		
  		if($x > 0) $this -> setData( 0 , $x );
  		else if( $x < -1) $this -> setData( 0 , $x + $this -> DV );
  		else $this -> t = 0;
	}
	
	private function fromString( $s , $b ) {
		$k;
		if( $b == 16) $k = 4;
		else if( $b == 8) $k = 3;
		else if( $b == 256) $k = 8; // byte array
		else if( $b == 2) $k = 1;
  		else if( $b == 32) $k = 5;
  		else if( $b == 4) $k = 2;
  		else {
  			$this -> fromRadix( $s , $b ); 
  			return; 
		}
  
  		$this -> t = 0;
  		$this -> s = 0;
		
		$i = 0;
		
		if( is_array( $s ) ){
			$i = count( $s );
		}
		else{
			$i = strlen( $s );
		}
		
		$mi = false;
		$sh = 0;
		
  		while(--$i >= 0) {
    		$x = ( $k == 8) ? $s[ $i ] & 0xff : $this -> intAt( $s,$i );
			
    		if($x < 0) {
      			if( $s[ $i ]  == "-") $mi = true;
     				continue;
    		}
    		$mi = false;
    		if( $sh == 0)
     			$this -> setData( $this -> t++ , $x );
    		else if( $sh+$k > self::$DB) {
    			$this -> setData( $this -> t - 1, $this -> getData( $this -> t - 1 ) | ($x&((1<<( self::$DB - $sh))-1))<<$sh );
      			$this -> setData( $this -> t++ , ( $x>>( self::$DB - $sh)) );
    		}
    		else
     	 		$this -> setData( $this -> t - 1, $this -> getData( $this -> t - 1 ) | $x<<$sh );
    		
    		$sh += $k;
    		
    		if( $sh >= self::$DB) $sh -= self::$DB;
  		}
  
  		if( $k == 8 && ( $s[0]&0x80) != 0) {
    		$this -> s = -1;
    		if( $sh > 0) $this -> setData( $this -> t - 1, $this -> getData( $this -> t - 1 ) | ((1<<( self::$DB - $sh))-1)<<$sh );
  		}
		
  		$this -> clamp();
		
  		if($mi) getZero() -> subTo( $this,$this);
	}

	public function clamp() {
  		$c = $this -> s & self::$DM;
  		while( $this -> t > 0 && $this -> getData( $this -> t-1 ) == $c ) -- $this -> t;
	}
	
	public function toString( $b ) {
  		if( $this -> s < 0) return "-" . $this -> negate() -> toString( $b );
  		$k;
  		if( $b == 16) $k = 4;
  		else if( $b == 8) $k = 3;
  		else if( $b == 2) $k = 1;
  		else if( $b == 32) $k = 5;
  		else if( $b == 4) $k = 2;
  		else return $this -> toRadix( $b );
		
  		$km = ( 1 << $k )-1; 
  		$d; 
  		$m = false;
		$r = "";
		$i = $this -> t;
		
 		$p = self::$DB - ( $i * self::$DB) % $k;
 	 	if( $i-- > 0) {
    		if( $p < self::$DB && ( $d = $this -> getData($i) >> $p) > 0) { $m = $true; $r = bigInteger::int2char($d); }
    		while( $i >= 0) {
    			if( $p < $k) {
        			$d = ( $this -> getData($i)&((1<<$p)-1))<<($k-$p);
        			$d |= $this -> getData(--$i)>>($p+= self::$DB - $k);
      			}
      			else {
        			$d = ( $this -> getData($i) >> ($p-=$k))&$km;
        			if( $p <= 0) { $p += self::$DB; --$i; }
      			}
      			if( $d > 0) $m = true;
      			if( $m ) $r .= bigInteger::int2char($d);
    		}
  		}
  		return $m?$r:"0";
	}
	
	private function negate() {
		$r = nbi(); 
		getZero() -> subTo( $this , $r ); 
		return r; 
	}
	
	public function absFunc() {
		 return ( $this -> s < 0 ) ? $this -> negate() : $this; 
	}
	
	public function compareTo( $a ){
		$r = $this -> s- $a -> s;
		if( $r != 0) return $r;
		$i = $this -> t;
		$r = $i-$a -> t;
  		if( $r != 0) return $r;
  		while(--$i >= 0) if(($r=$this -> getData($i)-$a -> getData( $i ) ) != 0) return $r;
  		return 0;
	}
	
	private function nbits( $x ) {
		$r = 1; 
		$t;
		
  		if( ( $t = $this -> zrsh( $x , 16 ) ) != 0) { $x = $t; $r += 16; }
  		if(($t=$x>>8) != 0) { $x = $t; $r += 8; }
  		if(($t=$x>>4) != 0) { $x = $t; $r += 4; }
  		if(($t=$x>>2) != 0) { $x = $t; $r += 2; }
  		if(($t=$x>>1) != 0) { $x = $t; $r += 1; }
		
  		return $r;
	}
	
	private function zrsh($a, $n) {
  		if ($n <= 0) return $a;
  		$b = 0x80000000;
  		return ($a >> $n) & ~($b >> ($n - 1));
	}
	
	public function bitLength() {
		if( $this -> t <= 0) return 0;
			
		return self::$DB * ( $this -> t - 1 ) + $this -> nbits( $this -> getData( $this -> t - 1 ) ^ ( $this -> s & self::$DM ) );
	}
	
	public function dlShiftTo( $n , $r ) {
  		for($i = $this -> t - 1; $i >= 0; --$i ) $r -> setData( $i + $n , $this -> getData($i) );
  		for( $i = $n - 1; $i >= 0; --$i) $r -> setData( $i , 0 );
  		$r -> t = $this -> t + $n;
  		$r -> s = $this -> s;
	}
	
	public function drShiftTo( $n , &$r ) {
		 for( $i = $n; $i < $this -> t; ++$i) $r -> setData( $i - $n , $this -> getData($i) );
		 
 		 $r -> t = max( ( $this -> t ) - $n , 0 );
 		 $r -> s = $this -> s;
	}
	
	private function lShiftTo( $n , $r ) {
		$bs = $n % self::$DB;
 	 	$cbs = self::$DB - $bs;
 		$bm = ( 1 << $cbs ) - 1;
  		$ds = floor( $n / self::$DB ); 
  		$c = ($this -> s<<$bs) & self::$DM;
  		$i;
  		for( $i = $this -> t - 1; $i >= 0; --$i ) {
    		$r -> setData( $i + $ds + 1 , ( $this -> getData($i) >> $cbs ) | $c );
    		$c = ( $this -> getData($i) & $bm ) << $bs;
  		}
  		for( $i = $ds - 1; $i >= 0; --$i ) $r -> setData( $i , 0 );
  		$r -> setData( $ds , $c );
  		$r -> t = $this -> t + $ds + 1;
  		$r -> s = $this -> s;
  		$r -> clamp();
	}
	
	private function rShiftTo( $n , $r ) {
  		$r -> s = $this -> s;
  		$ds = floor( $n / self::$DB);
  		if( $ds >= $this -> t) { $r -> t = 0; return; }
  		$bs = $n % self::$DB;
  		$cbs = self::$DB - $bs;
  		$bm = ( 1 << $bs ) - 1;
  		$r -> setData( 0 , $this -> getData( $ds ) >> $bs );
  		for( $i = $ds + 1; $i < $this -> t; ++$i) {
   	 		$r -> setData( $i - $ds - 1  , $r -> getData( $i - $ds - 1 )  | ( ( $this -> getData($i) & $bm ) << $cbs) );
    		$r -> setData( $i - $ds , $r -> getData( $i ) >> $bs );
  		}
  		if( $bs > 0) $r -> setData( $this -> t - $ds - 1 , $r -> getData( $this -> t - $ds - 1 ) | ( ( $this -> s & $bm ) << $cbs ) );
  		$r -> t = $this -> t - $ds;
  		$r -> clamp();
	}
	
	public function subTo( $a , $r ) {
		$i = 0;
		$c = 0; 
		$m = min( $a -> t, $this -> t );
 	 	while( $i < $m) {
    		$c += $this -> getData($i) - $a -> getData( $i );
    		$r -> setData( $i++ ,  $c & self::$DM );
    		$c >>= self::$DB;
  		}
  		if( $a -> t < $this -> t ) {
    		$c -= $a -> s;
    		while( $i < $this -> t) {
    			$c += $this -> getData($i);
      			$r -> setData( $i++ , $c & self::$DM );
      			$c >>= self::$DB;
    		}
    		$c += $this -> s;
  		}
  		else {
    		$c += $this -> s;
    		while( $i < $a -> t) {
    			$c -= $a -> getData( $i );
      			$r -> setData( $i++ , $c & $this -> DM );
      			$c >>= $this -> DB;
    		}
    		$c -= $a -> s;
  		}
  		$r -> s = ( $c < 0 ) ? -1:0;
  		if( $c < -1) $r -> setData( $i++ , $this -> DV + $c );
  		else if( $c > 0) $r -> setData( $i++ , $c );
		
  		$r -> t = $i;
  		$r -> clamp();
	}
	
	public function multiplyTo( $a , &$r ) {
  		$x = $this -> absFunc();
  		$y = $a -> absFunc();
  		$i = $x -> t;
  		$r -> t = $i + $y -> t;
  		while( --$i >= 0 ) $r -> setData( $i , 0 );
  		for( $i = 0; $i < $y -> t; ++$i ) $r -> setData( $i + $x -> t , $x -> am( 0, $y -> getData( $i ) , $r,$i,0,$x -> t ) );
  		$r -> s = 0;
  		$r -> clamp();
  		if($this -> s != $a -> s) getZero() -> subTo( $r , $r );
	}
	
	public function squareTo( $r ) {
		$x = $this -> absFunc();
		$i = $r -> t = 2*$x -> t;
  		while(--$i >= 0) $r -> setData( $i , 0 );
		
  		for( $i = 0; $i < $x -> t - 1; ++$i) {
    		$c = $x -> am( $i, $x -> getData( $i ) , $r , 2 * $i , 0 , 1 );
			$r -> addData( $i + $x -> t  , $x ->am( $i+1,2*$x -> getData( $i ) ,$r,2*$i+1,$c,$x -> t-$i-1) );
    		if( $r -> getData( $i + $x -> t ) >= self::$DV) {
     	 		$r -> subData( $i + $x -> t , self::$DV );
      			$r -> setData( $i + $x -> t + 1 , 1 );
    		}
  		}
		
  		if( $r -> t > 0) $r -> setData( $r -> t - 1 , $r -> getData( $r -> t - 1 ) + $x -> am( $i, $x -> getData( $i ) , $r , 2*$i , 0 , 1 ) );
  		$r -> s = 0;
  		$r -> clamp();
	}
	
	public function divRemTo( $m , $q , $r ) {
		$pm = $m -> absFunc();
  		if( $pm -> t <= 0) return;
  		$pt = $this -> absFunc();
  		if( $pt -> t < $pm -> t) {
    		if( $q != null) $q -> fromInt(0);
    		if( $r != null) $this -> copyTo( $r );
    		return;
  		}
  		if( $r == null) $r = nbi();
  		$y = nbi();
  		$ts = $this -> s;
		$ms = $m -> s;
  		$nsh = self::$DB - $this -> nbits( $pm -> getData( $pm -> t - 1) );    // normalize modulus
  		if( $nsh > 0) { $pm -> lShiftTo( $nsh , $y ); $pt -> lShiftTo( $nsh , $r); }
  		else { $pm -> copyTo( $y ); $pt -> copyTo( $r ); }
  		$ys = $y -> t;
  		$y0 = $y -> getData( $ys - 1 );
  		if( $y0 == 0) return;
  		$yt = $y0 * ( 1 << self::$F1 ) +( ( $ys > 1 ) ? $y -> getData( $ys - 2 ) >> self::$F2 : 0 );
  		$d1 = self::$FV / $yt;
  		$d2 = ( 1 << self::$F1 )/ $yt; 
  		$e = 1 << self::$F2;
  		$i = $r -> t; 
  		$j = $i - $ys;
		$t = ( $q == null ) ? nbi() : $q;
  		$y -> dlShiftTo( $j , $t );
  		if( $r -> compareTo( $t ) >= 0) {
    		$r -> setData( $r -> t++ , 1 );
    		$r -> subTo( $t , $r );
  		}
  		getOne() -> dlShiftTo( $ys , $t );
  		$t -> subTo( $y , $y );    // "negative" y so we can replace sub with am later
  		while( $y -> t < $ys ) $y -> setData( $y -> t++ , 0 );
  		while(--$j >= 0) {
    		// Estimate quotient digit
    		$qd = ( $r -> getData( --$i ) == $y0 ) ? $this -> DM : floor( $r -> getData( $i ) * $d1 + ( $r -> getData( $i - 1 ) + $e ) * $d2);
			$r -> setData( $i , $r -> getData( $i) + $y -> am( 0 , $qd , $r , $j , 0 , $ys ));
    		if( $r -> getData( $i ) < $qd ) {    // Try it out
    	 		$y -> dlShiftTo( $j , $t );
      			$r -> subTo( $t , $r );
      			while( $r -> getData( $i ) < --$qd ) $r -> subTo( $t , $r );
    		}
  		}
  		if($q != null) {
    		$r -> drShiftTo( $ys , $q );
    		if( $ts != $ms ) getZero() -> subTo( $q , $q);
  		}
  		$r -> t = $ys;
  		$r -> clamp();
  		if( $nsh > 0) $r -> rShiftTo( $nsh , $r );    // Denormalize remainder
  		if( $ts < 0) getZero() -> subTo( $r , $r );
	}

	private function mod( $a ) {
  		$r = nbi();
  		$this -> absFunc() -> divRemTo( $a , null , $r );
  		if( $this -> s < 0 && $r -> compareTo( getZero() ) > 0 ) $a -> subTo( $r , $r );
  		return $r;
	}
	
	public function invDigit() {
		if( $this -> t < 1 ) return 0;
  		$x = $this -> getData( 0 );
  		if(( $x & 1 ) == 0 ) return 0;
  		$y = $x & 3;        // y == 1/x mod 2^2
  		$y = ( $y*(2-($x&0xf)*$y))&0xf;    // y == 1/x mod 2^4
  		$y = ( $y*(2-($x&0xff)*$y))&0xff;    // y == 1/x mod 2^8
  		$y = ( $y*(2-((($x&0xffff)*$y)&0xffff)))&0xffff;    // y == 1/x mod 2^16
  		// last step - calculate inverse mod DV directly;
  		// assumes 16 < DB <= 32 and assumes ability to handle 48-bit ints
  		$y = ($y*(2-$x*$y% self::$DV))% self::$DV;        // y == 1/x mod 2^dbits
  		// we really want the negative inverse, and -DV < y < DV
  		return ($y > 0)? self::$DV-$y:-$y;
	}
	
	private function isEven() {
		return (( $this -> t > 0 ) ? ( $this -> getData( 0 ) & 1 ) : $this -> s ) == 0; 
	}
	
	private function expFunc( $e , $z ) {
		if( $e	-> getData( 0 ) > 0xffffffff || $e	-> getData( 0 ) < 1 ){
			return getOne();
		}
		
  		$r = nbi();
  		$r2 = nbi(); 
  		$g = $z -> convert( $this );
  		$i = $this -> nbits( $e	-> getData( 0 ) ) - 1;
		
  		$g -> copyTo( $r );
  		
  		while(--$i >= 0) {
   	 			
   	 		$z -> sqrTo( $r , $r2 );
    		
    		if(( $e -> getData( 0 )  & ( 1 << $i ) ) > 0){
    			$z -> mulTo( $r2 , $g , $r );
			}
    		else {
    			$t = $r; 
    			$r = $r2; 
    			$r2 = $t; 
			}
  		}
		
  		return $z -> revert( $r );
	}
	
	public function modPowInt( $e , $m ) {
  		$z;
		
  		if( $e	-> getData( 0 ) < 256|| $m -> isEven() ){
  			$z = new Classic( $m ); 	
		}
  		else{
  			$z = new Montgomery( $m );
		}
		
  		return $this -> expFunc( $e , $z );
	}
	
	private function cloneFunc(){
		 $r = nbi(); 
		 $this -> copyTo( $r ); 
		 return $r; 
	}
	
	private function intValue() {
		
  		if( $this -> s < 0) {
    		if( $this -> t == 1) return $this -> getData( 0 ) - $this -> DV;
    		else if( $this -> t == 0) return -1;
  		}
		
  		else if( $this -> t == 1) return $this -> getData( 0 );
  		else if( $this -> t == 0) return 0;
		
  		return (( $this -> getData( 1 ) & ( ( 1 << ( 32 - $this -> DB ) ) - 1 ) ) << $this -> DB ) | $this -> getData( 0 );
	}
	
	private function byteValue() {
		 return ( $this -> t == 0 ) ? $this -> s : ( $this -> getData( 0 ) << 24 ) >> 24; 
	}
	
	private function shortValue() {
		return ( $this -> t == 0 ) ? $this -> s : ( $this -> getData( 0 ) << 16 ) >> 16; 
	}
	
	private function chunkSize( $r ){
		 return floor( log( 2.0 ) * $this -> DB / log( $r ) ); 
	}
	
	private function signum() {
  		if( $this -> s < 0) return -1;
  		else if( $this -> t <= 0 || ( $this -> t == 1 && $this -> getData( 0 ) <= 0) ) return 0;
 	 	else return 1;
	}
	
	private function toRadix( $b ) {
		if( $b == null) $b = 10;
  		if( $this -> signum() == 0 || $b < 2 || $b > 36) return "0";
  		$cs = $this -> chunkSize( $b );
  		$a = pow( $b , $cs );
  		$d = nbv( $a ); 
  		$y = nbi(); 
  		$z = nbi();
  		$r = "";
		
  		$this -> divRemTo( $d , $y , $z );
  		while( $y -> signum() > 0) {
  			$object	=	$a + $z -> intValue();
    		$r = substr( $object -> toString( $b ) , 1 ) + $r;
    		$y -> divRemTo( $d , $y , $z );
  		}
  		return $z -> intValue() -> toString( $b ) + $r;
	}
	
	private function fromRadix( $s , $b ) {
  		$this -> fromInt(0);
  		if( $b == null ) $b = 10;
  		$cs = $this -> chunkSize( $b );
  		$d = pow( $b , $cs );
		$mi = false;
		$j = 0;
		$w = 0;
  		for( $i = 0; $i < strlen( $s ); ++$i ) {
    		$x = $this -> intAt( $s , $i );
    		if( $x < 0) {
      			if( $s[ $i ] == "-" && $this -> signum() == 0 ) $mi = true;
      			continue;
    		}
    		$w = $b * $w + $x;
    		if(++$j >= $cs) {
      			$this -> dMultiply( $d );
      			$this -> dAddOffset( $w , 0 );
      			$j = 0;
      			$w = 0;
    		}
  		}
  		if( $j > 0) {
    		$this -> dMultiply( pow( $b , $j ) );
    		$this -> dAddOffset( $w , 0 );
  		}
  		if( $mi ) getZero() -> subTo( $this , $this );
	}
	
	private function fromNumber( $a , $b , $c ) {
  		if( gettype( $b ) == "integer" ) {
    		if( $a < 2 ) $this -> fromInt( 1 );
    		else {
      			$this -> fromNumber( $a , $c , null );
				
      			if( !( $this -> testBit( $a - 1 ) ) ){    // force MSB set
        			$this -> bitwiseToOr( getOne() -> shiftLeft( $a - 1 ) , $this );
				}
				
      			if( $this -> isEven() ) $this -> dAddOffset( 1 , 0 ); // force odd
      			
      			while( !( $this -> isProbablePrime( $b ) ) ) {
        			$this -> dAddOffset( 2 , 0 );
        			if( $this -> bitLength() > $a ) $this -> subTo( getOne() -> shiftLeft( $a - 1 ) , $this );
      			}
    		}
  		}
  		else {
    		$x = array();
    		$t = $a & 7;
    		$this -> nextBytes( $x , ( $a >> 3 ) + 1 );
    		if( $t > 0) $x[ 0 ] &= (( 1 << $t ) - 1 ); 
    		else $x[0] = 0;
    		
    		$this -> fromString( $x , 256 );
  		}
	}
	
	private function nextBytes( &$x , $iLen ){
		
		for( $i = 0 ; $i < $iLen ; $i++ ){
			srand();
			$x[ $i ]	=	rand();	
		}
		
	}
	
	private function toByteArray() {
		$i = $this -> t;
		$r = array();
  		$r[ 0 ] = $this -> s;
  		$p = $this -> DB - ( $i * $this -> DB ) % 8;
		$d;
		$k = 0;
  		if( $i-- > 0 ) {
    		if( $p < $this -> DB && ( $d = $this -> getData($i) >> $p ) != ( $this -> s & $this -> DM) >> $p){
     			$r[ $k++ ] = $d | ( $this -> s << ( $this -> DB - $p ) );
			}
    		while( $i >= 0 ) {
      			if( $p < 8 ) {
        			$d = ( $this -> getData($i) & ( ( 1 << $p) - 1 ) ) << ( 8 - $p );
        			$d |= $this -> getData( --$i ) >> ( $p += $this -> DB - 8 );
      			}
      			else {
        			$d = ( $this -> getData($i) >> ( $p -= 8) ) & 0xff;
        			if( $p <= 0 ) { $p += $this -> DB; --$i; }
      			}
      			if(( $d & 0x80) != 0) $d |= -256;
      			if( $k == 0 && ( $this -> s & 0x80) != ( $d & 0x80)) ++$k;
      			if( $k > 0 || $d != $this -> s) $r[ $k++ ] = $d;
    		}
  		}
  		return $r;
	}
	
	private function equals( $a ) { return( $this -> compareTo( $a ) == 0); }
	private function minFunc( $a ) { return( $this -> compareTo($a) < 0)? $this : $a; }
	private function maxFunc( $a ) { return( $this -> compareTo($a) > 0)? $this : $a; }
	
	private function bitwiseTo( $a , $op , $r ) {
  		$i;
  		$f;
  		$m = min( $a -> t , $this -> t );
  		for( $i = 0; $i < $m; ++$i) $r -> setData( $i , $op( $this -> getData($i) , $a -> getData( $i ) ) );
  		
  		if( $a -> t < $this -> t) {
    		$f = $a -> s & $this -> DM;
    		for( $i = $m; $i < $this -> t; ++$i ) $r -> setData( $i , $op( $this -> getData( $i ) , $f ) );
    		$r -> t = $this -> t;
  		}
  		else {
    		$f = $this -> s & $this -> DM;
    		for( $i = $m; $i < $a -> t; ++$i ) $r -> setData( $i , $op( $f , $a -> getData( $i ) ) );
    		$r -> t = $a -> t;
  		}
  		$r -> s = $op( $this -> s , $a  -> s );
  		$r -> clamp();
	}
	
	private function bitwiseToOr( $a , $r ) {
  		$i;
  		$f;
  		$m = min( $a -> t , $this -> t );
  		for( $i = 0; $i < $m; ++$i) $r -> setData( $i , $this -> op_or( $this -> getData($i) , $a -> getData( $i ) ) );
  		
  		if( $a -> t < $this -> t) {
    		$f = $a -> s & $this -> DM;
    		for( $i = $m; $i < $this -> t; ++$i ) $r -> setData( $i , $this -> op_or( $this -> getData( $i ) , $f ) );
    		$r -> t = $this -> t;
  		}
  		else {
    		$f = $this -> s & self::$DM;
    		for( $i = $m; $i < $a -> t; ++$i ) $r -> setData( $i , $this -> op_or( $f , $a -> getData( $i ) ) );
    		$r -> t = $a -> t;
  		}
  		$r -> s = $this -> op_or( $this -> s , $a  -> s );
  		$r -> clamp();
	}
	
	public function op_and( $x , $y ) { return $x & $y; }
	private function andFunc( $a ) { $r = nbi(); $this -> bitwiseTo( $a , $this -> op_and , $r ); return $r; }

	public function op_or( $x , $y ) { return $x | $y; }
	private function orFunc( $a ) { $r = nbi(); $this -> bitwiseTo( $a , $this -> op_or ,$r ); return $r; }

	public function op_xor( $x , $y ) { return $x ^ $y; }
	private function xorFunc($a) { $r = nbi(); $this -> bitwiseTo( $a , $this -> op_xor , $r ); return $r; }

	public function op_andnot( $x , $y ) { return $x & ~$y; }
	private function andNot( $a ) { $r = nbi(); $this -> bitwiseTo( $a , $this -> op_andnot , $r ); return $r; }
	
	private function not() {
  		$r = nbi();
  		for( $i = 0; $i < $this -> t; ++$i) $r -> setData($i, $this -> DM & ~( $this -> getData($i) ) );
  		$r -> t = $this -> t;
  		$r -> s = ~$this -> s;
 	 	return $r;
	}
	
	private function shiftLeft( $n ) {
		$r = nbi();
  		if($n < 0) $this -> rShiftTo( -$n , $r ); else $this -> lShiftTo( $n , $r );
  		return $r;
	}
	
	private function shiftRight( $n ) {
		$r = nbi();
  		if( $n < 0 ) $this -> lShiftTo( -$n , $r ); else $this -> rShiftTo( $n , $r );
  		return $r;
	}
	
	private function getLowestSetBit() {
  		for( $i = 0; $i < $this -> t; ++$i){
    		if( $this -> getData($i) != 0 ) return $i * self::$DB + lbit( $this -> getData($i) );
		}
  		if( $this -> s < 0) return $this -> t * self::$DB;
  		return -1;
	}
	
	private function bitCount() {
  		$r = 0;
		$x = $this -> s & $this -> DM;
  		for( $i = 0; $i < $this -> t; ++$i ) $r += cbit( $this -> getData($i) ^ $x );
  		return $r;
	}
	
	private function testBit( $n ) {
  		$j = floor( $n / self::$DB);
  		if( $j >= $this -> t) return ( $this -> s != 0);
  		return( ( $this -> getData($j) & ( 1 << ( $n % self::$DB ) ) ) != 0 );
	}
	
	private function changeBit( $n , $op ) {
  		$r = getOne() -> shiftLeft( $n );
  		$this -> bitwiseTo( $r , $op , $r );
  		return $r;
	}
	
	private function setBit( $n ) { return $this -> changeBit( $n , $this -> op_or ); }
	
	private function clearBit( $n ) { return $this -> changeBit( $n , $this -> op_andnot ); }
	
	private function flipBit( $n ) { return $this -> changeBit( $n , $this -> op_xor); }
	
	private function addTo( $a , $r ) {
  		$i = 0;
  		$c = 0;
  		$m = min( $a -> t , $this -> t);
  		while( $i < $m ) {
    		$c += $this -> getData($i) + $a -> getData( $i );
    		$r -> setData( $i++ ,  $c & $this -> DM );
    		$c >>= $this -> DB;
  		}
  		if( $a -> t < $this -> t ) {
    		$c += $a -> s;
    		while( $i < $this -> t) {
      			$c += $this -> getData($i);
      			$r -> setData( $i++ , $c & $this -> DM );
      			$c >>= $this -> DB;
    		}
    		$c += $this -> s;
  		}
  		else {
    		$c += $this -> s;
    		while( $i < $a -> t) {
      			$c += $a -> getData( $i );
      			$r -> setData( $i++ , $c & $this -> DM );
      			$c >>= $this -> DB;
    		}
    		$c += $a -> s;
  		}
  		$r -> s = ( $c < 0 ) ? -1 : 0;
  		if( $c > 0 ) $r -> setData( $i++ , $c );
  		else if( $c < -1) $r -> setData( $i++ , $this -> DV + $c );
  		$r -> t = $i;
  		$r -> clamp();
	}
	
	private function add( $a ) { $r = nbi(); $this -> addTo( $a , $r ); return $r; }

	private function subtract( $a ) { $r = nbi(); $this -> subTo( $a , $r ); return $r; }

	private function multiply( $a ) { $r = nbi(); $this -> multiplyTo( $a , $r ); return $r; }

	private function divide( $a ) { $r = nbi(); $this -> divRemTo( $a , $r , null ); return $r; }

	private function remainder( $a ) { $r = nbi(); $this -> divRemTo( $a , null , $r ); return $r; }
	
	private function divideAndRemainder( $a ) {
  		$q = nbi();
  		$r = nbi();
 	 	$this -> divRemTo( $a , $q , $r );
  		return array( $q , $r );
	}
	
	private function dMultiply( $n ) {
  		$this -> setData( $this -> t , $this -> am( 0 , $n - 1 , $this , 0 , 0 , $this -> t ) );
  		++$this -> t;
  		$this -> clamp();
	}
	
	private function dAddOffset( $n , $w ) {
  		while( $this -> t <= $w ) $this -> setData( $this -> t ++ , 0 );
  		$this -> addData( $w , $n );
  		while( $this -> getData( $w ) >= self::$DV ) {
    		$this -> subData( $w , $this -> DV );
    		if( ++$w >= $this -> t) $this -> setData( $this -> t++ , 0 );
    		$this -> addData( $w , 1 );
  		}
	}
	
	private function powFunc( $e ) { return $this -> expFunc( $e , new NullExp() ); }
	
	private function multiplyLowerTo( $a , $n , $r ) {
  		$i = min( $this -> t + $a -> t , $n );
  		$r -> s = 0;
  		$r -> t = $i;
  		while( $i > 0) $r -> setData( --$i , 0 );
  		$j;
  		for( $j = $r -> t - $this -> t; $i < $j; ++$i) $r -> setData( $i + $this -> t , $this -> am( 0, $a -> getData( $i ) , $r , $i , 0 , $this -> t) );
  		for( $j = min( $a -> t , $n ); $i < $j; ++$i ) $this -> am( 0, $a -> getData( $i ) , $r , $i , 0 , $n - $i );
  		$r -> clamp();
	}
	
	private function multiplyUpperTo( $a , $n , $r ) {
  		--$n;
  		$i = $r -> t = $this -> t + $a -> t - $n;
  		$r -> s = 0; // assumes a,this >= 0
  		while( --$i >= 0) $r -> setData( $i , 0 );
  		for( $i = max( $n - $this -> t , 0 ); $i < $a -> t; ++$i){
    		$r -> setDaat( $this -> t + $i - $n , $this -> am( $n - $i, $a -> getData( $i ) , $r , 0 , 0 ,$this -> t + $i - $n ) );
		}
  		$r -> clamp();
  		$r -> drShiftTo( 1 , $r );
	}
	
	private function modPow( $e , $m ) {
  		$i = $e -> bitLength();
  		$k;
  		$r = nbv( 1 );
  		$z;
		
  		if( $i <= 0) return $r;
  		else if( $i < 18) $k = 1;
  		else if( $i < 48) $k = 3;
  		else if( $i < 144) $k = 4;
  		else if( $i < 768) $k = 5;
  		else $k = 6;
  		if( $i < 8){
    		$z = new Classic( $m );
		}
  		else if( $m -> isEven()){
  	 		$z = new Barrett( $m );
		}
  		else{
    		$z = new Montgomery( $m );
		}

  		$g = array();
  		$n = 3;
  		$k1 = $k - 1;
  		$km = ( 1 << $k ) - 1;
		
  		$g[ 1 ] = $z -> convert( $this );
		
  		if( $k > 1) {
			$g2 = nbi();
    		$z -> sqrTo( $g[1] , $g2 );
    		while( $n <= $km ) {
    	 		$g[ $n ] = nbi();
      			$z -> mulTo( $g2 , $g[ $n - 2 ] , $g[ $n ] );
      			$n += 2;
    		}
  		}

  		$j = $e -> t - 1;
  		$w;
  		$is1 = true;
  		$r2 = nbi();
  		$t;
		
  		$i = nbits( $e -> getData( $j ) ) - 1;
		
  		while( $j >= 0 ) {
    		if( $i >= $k1 ) $w = ( $e -> getData( $j ) >> ( $i - $k1 ) ) & $km;
    		else {
      			$w = ( $e -> getData( $j ) & ( ( 1 << ( $i + 1 ) ) - 1 ) ) << ( $k1 - $i );
      			if( $j > 0) $w |= $e -> getData( $j - 1 ) >> ( $this -> DB + $i - $k1 );
    		}

    		$n = $k;
    		while(( $w & 1) == 0) { $w >>= 1; --$n; }
			
    		if(($i -= $n) < 0) { $i += $this -> DB; --$j; }
    		if( $is1 ) {
      			$g[ $w ] -> copyTo( $r );
      			$is1 = false;
    		}
    		else {
      			while( $n > 1 ) { $z -> sqrTo( $r , $r2 ); $z -> sqrTo( $r2 , $r ); $n -= 2; }
      			if( $n > 0 ) $z -> sqrTo( $r , $r2 ); else { $t = $r; $r = $r2; $r2 = $t; }
      			$z -> mulTo( $r2 , $g[ $w ] , $r );
    		}

    		while( $j >= 0 && ( $e -> getData( $j ) & ( 1 << $i ) ) == 0) {
      			$z -> sqrTo( $r , $r2 ); $t = $r; $r = $r2; $r2 = $t;
      			if( --$i < 0 ) { $i = $this -> DB - 1; --$j; }
    		}
  		}
  		return $z -> revert( $r );
	}
	
	private function gcd( $a ) {
  		$x = ( $this -> s < 0 ) ? $this -> negate() : $this -> cloneFunc();
  		$y = ( $a -> s < 0 ) ? $a -> negate(): $a -> cloneFunc();
  		if( $x -> compareTo( $y ) < 0 ) { $t = $x; $x = $y; $y = $t; }
  		$i = $x -> getLowestSetBit();
  		$g = $y -> getLowestSetBit();
  		if( $g < 0) return $x;
  		if( $i < $g) $g = $i;
  		if( $g > 0) {
    		$x -> rShiftTo( $g , $x );
    		$y -> rShiftTo( $g , $y );
  		}
  		while( $x -> signum() > 0) {
    		if(( $i = $x -> getLowestSetBit()) > 0) $x -> rShiftTo( $i , $x );
    		if(( $i = $y -> getLowestSetBit()) > 0) $y -> rShiftTo( $i , $y );
    		if( $x -> compareTo( $y ) >= 0) {
      			$x -> subTo( $y , $x );
      			$x -> rShiftTo( 1 , $x );
    		}
    		else {
      			$y -> subTo( $x , $y );
      			$y -> rShiftTo( 1 , $y );
    		}
  		}
  		if( $g > 0 ) $y -> lShiftTo( $g , $y );
  		return $y;
	}
	
	private function modInt( $n ) {
  		if( $n <= 0 ) return 0;
  		$d = self::$DV % $n;
  		$r = ( $this -> s < 0 ) ? $n-1 : 0;
  		if( $this -> t > 0){
    		if( $d == 0 ) $r = $this -> getData( 0 ) % $n;
    		else for( $i = $this -> t - 1; $i >= 0; --$i ) $r = ( $d * $r + $this -> getData($i) ) % $n;
			
		}
  		return $r;
	}
	
	function modInverse( $m ) {
  		$ac = $m -> isEven();
  		if(( $this -> isEven() && $ac ) || $m -> signum() == 0) return getZero();
  		$u = $m -> cloneFunc();
  		$v = $this -> cloneFunc();
  		$a = nbv( 1 );
  		$b = nbv( 0 );
  		$c = nbv( 0 );
  		$d = nbv( 1 );
  		while( $u -> signum() != 0) {
    		while( $u -> isEven()) {
      			$u -> rShiftTo( 1 , $u );
      			if( $ac ) {
        			if( !$a -> isEven() || !$b -> isEven()) { $a -> addTo( $this , $a ); $b -> subTo( $m , $b ); }
        			$a -> rShiftTo( 1 , $a );
      			}
      			else if(! $b -> isEven()) $b -> subTo( $m , $b );
      			$b -> rShiftTo( 1 , $b );
    		}
    		while( $v -> isEven()) {
      			$v -> rShiftTo( 1 , $v );
      			if( $ac ) {
        			if(! $c -> isEven() || !$d -> isEven()) { $c -> addTo( $this , $c ); $d -> subTo( $m , $d ); }
        			$c -> rShiftTo( 1 , $c );
      			}
      			else if(!$d -> isEven()) $d -> subTo( $m , $d );
      			$d -> rShiftTo( 1 , $d );
    		}
    		if( $u -> compareTo( $v ) >= 0) {
      			$u.subTo( $v , $u );
      			if( $ac ) $a -> subTo( $c , $a );
      			$b -> subTo( $d , $b );
    		}
    		else {
      			$v -> subTo( $u , $v );
      			if( $ac ) $c -> subTo( $a , $c );
      			$d -> subTo( $b , $d );
    		}
		}
  		if( $v -> compareTo( getOne() ) != 0 ) return getZero();
 	 	if( $d -> compareTo( $m ) >= 0) return $d -> subtract( $m );
  		if( $d -> signum() < 0) $d -> addTo( $m , $d ); else return $d;
  		if( $d -> signum() < 0) return $d -> add( $m ); else return $d;
	}

	private function isProbablePrime( $t ) {
  		$i;
  		$x = $this -> absFunc();
		
  		if( $x -> t == 1 && $x -> getData( 0 ) <= getPrime( getPrimeLen() - 1 ) ) {
    		for( $i = 0; $i < getPrimeLen(); ++$i ){
      			if( $x -> getData( 0 ) == getPrime( $i ) ) return true;
			}
    		return false;
  		}
  		if( $x -> isEven()) return false;
  		$i = 1;
  		while( $i < getPrimeLen() ) {
    		$m = getPrime( $i );
    		$j = $i + 1;
    		while( $j < getPrimeLen() && $m < getLPLIM() ) $m *= getPrime( $j++ );
    		$m = $x -> modInt( $m );
    		while( $i < $j ) if( $m % getPrime( $i++ ) == 0) return false;
  		}
  		return $x -> millerRabin( $t );
	}
	
	private function millerRabin( $t ) {
  		$n1 = $this -> subtract( getOne() );
  		$k = $n1 -> getLowestSetBit();
  		if( $k <= 0 ) return false;
  		$r = $n1 -> shiftRight( $k );
  		$t = ( $t + 1 ) >> 1;
  		if( $t > getPrimeLen() ) $t = getPrimeLen();
  		$a = nbi();
  		for( $i = 0; $i < $t; ++$i ) {
    		$a -> fromInt( getPrime( $i ) );
    		$y = $a -> modPow( $r , $this );
    		if( $y -> compareTo( getOne() ) != 0 && $y -> compareTo( $n1 ) != 0) {
      			$j = 1;
      			while( $j++ < $k && $y -> compareTo( $n1 ) != 0) {
        			$y = $y -> modPowInt( 2 , $this );
        			if( $y -> compareTo( getOne() ) == 0) return false;
      			}
      			if( $y -> compareTo( $n1 ) != 0 ) return false;
    		}
  		}
  		return true;
	}
	
	public function getArray(){
		return $this -> iData;
	}
	
}

function lbit( $x ) {
  	if( $x == 0) return -1;
  	$r = 0;
  	if(( $x & 0xffff) == 0) { $x >>= 16; $r += 16; }
  	if(( $x & 0xff) == 0) { $x >>= 8; $r += 8; }
	if(( $x & 0xf) == 0) { $x >>= 4; $r += 4; }
  	if(( $x & 3) == 0) { $x >>= 2; $r += 2; }
	if(( $x & 1) == 0) ++$r;
	return $r;
}

function cbit( $x ) {
	$r = 0;
  	while( $x != 0 ) { $x &= $x - 1; ++$r; }
  	return $r;
}

function nbi(){
	return new bigInteger();	
}

function nbv( $i ) {
	$r = nbi(); 
	$r -> fromInt( $i ); 
	return $r; 
}

$ZERO	=	nbv( 0 );
$ONE	=	nbv( 1 );

function getZero(){
	
	global $ZERO;	
		
	return $ZERO;
}
	
function getOne(){
	
	global $ONE;	
		
	return $ONE;
}

$LOWPRIMES	=	array( 2,3,5,7,11,13,17,19,23,29,31,37,41,43,47,53,59,61,67,71,73,79,83,89,97,101,103,107,109,113,127,131,137,139,149,151,157,163,167,173,179,181,191,193,197,199,211,223,227,229,233,239,241,251
			,257,263,269,271,277,281,283,293,307,311,313,317,331,337,347,349,353,359,367,373,379,383,389,397,401,409,419,421,431,433,439,443,449,457,461,463,467,479,487,491,499,503,509 );
$LPLIM		=	( 1 << 26 ) / $LOWPRIMES[ count( $LOWPRIMES ) - 1];

function getPrime( $i ){
	
	global $LOWPRIMES;
	
	return $LOWPRIMES[ $i ];
	
}

function getPrimeLen(){
	
	global $LOWPRIMES;
	
	return count( $LOWPRIMES );
	
}


function getLPLIM(){
	
	global $LPLIM;
	
	return $LPLIM;
	
}

function printObject( $obj ){
	print_r( $obj -> getArray() );
}

?>