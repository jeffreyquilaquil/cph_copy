var game = new Phaser.Game(1100, 420, Phaser.AUTO);
var puzzle;

var cols;
var rows;
var piece;
var keys;
var drag = false;
var timer = 0;
var text = 0;
var numPieces;
var finish = 0;
var timeEvent;


var GameState = {
	preload: function(){
		this.load.image('puzzle','asset/images/background.jpg');
		this.load.spritesheet('puzzlesheet','asset/images/background.jpg', 128, 105);
	},
	create: function(){
		game.stage.backgroundColor = '#cccccc';
		puzzle = game.add.sprite(0, 0,'puzzle');
		puzzle.alpha = 0.2;

		createPuzzlePieces();

		text = game.add.text(950, 20, 'Timer: 0', {align: 'center'});
		timeEvent = game.time.events.loop(Phaser.Timer.SECOND, puzzleTimer, this);
		
	},
	update: function(){
		if(finish == numPieces){
			game.time.events.remove(timeEvent);
			
			var gameEnd = game.add.text(game.world.centerX, game.world.centerY, 'CONGRATULATIONS', {align: 'center', fontSize: '40px'});
			gameEnd.anchor.setTo(0.5);
		}
	}
};

function puzzleTimer(){
	timer++;
	text.setText('Timer: ' + timer);
}

function createPuzzlePieces(){
	cols = Math.floor(640 / 128);
	rows = Math.floor(game.world.height / 105);

	numPieces = cols * rows;
	var i,j, piecesIndex = 0;
	

	var shuffleArray = createIndexArray(numPieces);

	piecesGroup = game.add.group();

	for(i = 0; i < rows; i++){
		for(j = 0; j < cols; j++){

            piece = piecesGroup.create( 650 + ( 3 * Math.random() * 100 ), 80 + (3 * Math.random() * 50), "puzzlesheet", shuffleArray[piecesIndex]);

            piece.name = 'piece-'+(j)+'-'+(i);
            piece.destIndex = shuffleArray[piecesIndex];
            piece.inputEnabled = true;
            piece.input.enableDrag(false, true);

            piece.events.onDragStop.add(onDragStop, this);
            piece.input.enableSnap(128,105, false,true);

            piecesIndex++;
		}
	}

}

function onDragStop(pieceSprite){
	var name = 0;
	name = pieceSprite.name.split('-');
	var positionX = name[1] * 128;
	var positionY = name[2] * 105;

	console.log(positionX);

	var anchorX = pieceSprite.x;
	var anchorY = pieceSprite.y;

	console.log(anchorX);

	if( anchorX == positionX && positionY == anchorY ){
		finish+=1;
		pieceSprite.input.draggable = false;
	}
}


function createIndexArray(numPieces) {

    var indexArray = [];

    for (var i = 0; i < numPieces; i++)
    {
        indexArray.push(i);
    }

    return indexArray;
}

game.state.add('GameState', GameState);
game.state.start('GameState');