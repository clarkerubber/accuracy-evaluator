<?php

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

function main ( $user, $rated = 1, $nb = 100, $url = "http://en.lichess.org/api/game" ) {
	if ( ( $games = json_decode( file_get_contents( "$url?username=$user&rated=$rated&&analysed=1&with_analysis=1&nb=$nb" ), TRUE ) ) !== FALSE ) { 
		foreach ( $games['list'] as $key => $game ) {
			$side = ( $game['players']['white']['userId'] == $user )? 'white' : 'black';
			$loss = eval_loss ( $game['analysis'],  $side );
			echo $game['players']['white']['userId']." VS ".$game['players']['black']['userId']."\n";
			echo str_pad( intval ( $loss ), 4, ' ', STR_PAD_LEFT) .' '. str_pad ( '', intval ( $loss/10 ), '=' ) ."\n\n";
		}
	}

}

if ( isset ( $argv[1] ) ) {
	main ( $argv[1] );
}