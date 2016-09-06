var game = new Phaser.Game(1100, 420, Phaser.AUTO);

var sizeHeight = 105;
var sizeWidth = 128;
var puzzle, text, timeEvent, timer = 0;
var cols, rows, numPieces, piecesGroup, piece, finish = 0;

var GameState = {
	preload: function(){
		this.load.image('puzzle', 'asset/images/background.jpg');
		this.load.spritesheet('puzzleSheet', 'asset/images/background.jpg', sizeWidth, sizeHeight);
	},
	create: function(){
		game.stage.backgroundColor = '#cccccc';
		puzzle = game.add.sprite(0,0,'puzzle');



		puzzle.alpha = 0.3;

		createPuzzlePieces();

		text = game.add.text(950, 20, 'Timer: 0', {align: 'center'});
		timeEvent = game.time.events.loop(Phaser.Timer.SECOND, puzzletimer, this);

	},
	update: function(){
		if( finish == numPieces ){
			game.time.events.remove(timeEvent);

			var gameEnd = game.add.text(game.world.centerX,game.world.centerY, "CONGRATULATIONS!", {align: 'center', fontSize: '40px'} );
			gameEnd.anchor.setTo(0.5);
		}
	}
};

function puzzletimer(){
	timer++;
	text.setText('Timer: '+timer);
}

function createPuzzlePieces(){
	cols = Math.floor( 640 / sizeWidth );
	rows = Math.floor( 420 / sizeHeight );

	numPieces = cols * rows;
	
	var i,j, piecesIndex=0;

	var shuffleArray = createIndexArray(numPieces);

	piecesGroup = game.add.group();

	for(i = 0; i < rows; i++){
		for(j = 0; j < cols; j++){
			piece = piecesGroup.create( 650 + (Math.random() * 3 * 100), 80 + (Math.random() * 3 * 70),'puzzleSheet', shuffleArray[piecesIndex] );

			piecesIndex++;
			piece.name = 'piece-'+(j)+'-'+(i);
			piece.inputEnabled = true;
			piece.input.enableDrag(false, true);
			piece.posX = j;
			piece.posY = i;

			piece.events.onDragStop.add(dragStopEvent, this);
			piece.input.enableSnap(sizeWidth,sizeHeight, false, true);
		}
	}
}

function dragStopEvent(pieceSprite){
	var name = 0;

	name = pieceSprite.name.split('-');
	var positionX = name[1] * sizeWidth;
	var positionY = name[2] * sizeHeight;

	var anchorX = pieceSprite.x;
	var anchorY = pieceSprite.y;

	if( anchorX == positionX && anchorY == positionY){
		finish++;
		pieceSprite.input.draggable = false;
	}
}

function createIndexArray(numPieces){
	var indexArray = [];

	for( var i = 0; i < numPieces; i++){
		indexArray.push(i);
	}

	return indexArray;
}

game.state.add('GameState', GameState);
game.state.start('GameState');