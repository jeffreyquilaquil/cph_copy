var game = new Phaser.Game(600, 420, Phaser.AUTO);
var andrew;
var keys;
var drag = false;

var GameState = {
	preload: function(){
		this.load.image('andrew', 'asset/images/vrubio.jpg');
	},
	create: function(){
		game.stage.backgroundColor = '#cccccc';
		andrew = game.add.sprite(game.world.centerX,game.world.centerY,'andrew');
		andrew.anchor.setTo(0.5);

		andrew.inputEnabled = true;
		andrew.input.enableDrag();

		andrew.events.onDragStart.add(onDragStart,this);
		andrew.events.onDragStop.add(onDragStop,this);

		keys = game.input.keyboard.addKeys({'up': Phaser.KeyCode.W, 'down': Phaser.KeyCode.S,'left': Phaser.KeyCode.A,'right': Phaser.KeyCode.D});
	},
	update: function(){
		if( drag ){
			andrew.angle += 1;
		}
		if(keys.up.isDown){
			andrew.y -= 1;
		}
		if(keys.down.isDown){
			andrew.y += 1;
		}
		if(keys.left.isDown){
			andrew.x -= 1;
		}
		if(keys.right.isDown){
			andrew.x += 1;
		}
	}
};

function onDragStart(){
	drag = true;
}
function onDragStop(){
	drag = false;
}
game.state.add('GameState', GameState);
game.state.start('GameState');