<div id="loginpage">
	<h1>LOG IN HERE</h1>
	<p class="errortext"><?php if(isset($error)){ echo $error; } ?></p>
	<form action="<?= $this->config->base_url() ?>login/" method="POST">
	<table>
		<tr>
			<td>Username:</td>
			<td><input type="text" name="username"/></td>
		</tr>
		<tr>
			<td>Password:</td>
			<td><input type="password" name="password"/></td>
		</tr>
		<tr>
			<td><br/></td>
			<td>
				<input type="hidden" name="urlRequest" value="<?= $_SERVER['REQUEST_URI'] ?>"/>
				<input type="submit" value="Log in"/><br/><br/>
				<a href="<?= $this->config->base_url().'forgotpassword/' ?>" class="iframe">Forgot Password?</a>
			</td>
		</tr>
	</table>
	</form>
</div>