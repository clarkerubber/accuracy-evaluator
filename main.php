<?php

function main ( $user, $nb = 100, $url = "http://en.lichess.org/api/game" ) {
	if ( ( $games = json_decode( file_get_contents( "$url?username=$user&rated=1&nb=$nb" ), TRUE ) ) !== FALSE ) { 
		print_r( $games );
	}

}

//main( "Clarkey" );

function eval_loss ( $moves, $side ) {
	// White, is the sum of changes between blacks turn and white turn (even => odd)
	// Black, is the sum of changes between whites turn and blacks turn (odd => even)

	$withEval = 0;
	$net = 0;

	$match = 0;
	if ( $side == 'white' ) {
		$match = 1;
	}

	foreach ( $moves as $ply => $move ) {
		if ( $ply%2 == $match && isset ( $move['eval'] ) &&  isset ( $moves[$ply + 1]['eval'] ) ) {
			$move['eval'] = ($move['eval'] > 1000)? 1000 : ( ($move['eval'] < -1000)? -1000 : $move['eval'] );
			$moves[$ply + 1]['eval'] = ($moves[$ply + 1]['eval'] > 1000)? 1000 : ( ($moves[$ply + 1]['eval'] < -1000)? -1000 : $moves[$ply + 1]['eval'] );

			$net += $move['eval'] - $moves[$ply + 1]['eval'];
			$withEval ++;
		}
	}
	
	return ($side == 'white')? $net / $withEval : -1 * $net / $withEval;
}

function stub ( $user, $file = "stub/thibault-games.json" ) {
	if ( ( $games = json_decode ( file_get_contents ( $file ), TRUE ) ) !== FALSE ) {

		foreach ( $games['list'] as $key => $game ) {
			$side = ( $game['players']['white']['userId'] == $user )? 'white' : 'black';
			$loss = eval_loss ( $game['analysis'],  $side );
			echo str_pad( intval ( $loss ), 4, ' ') . str_pad ( '', intval ( $loss ), '=' ) ."\n";
		}
	}
}

stub('thibault');