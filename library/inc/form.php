<p>To register for API usage please enter the following information.</p>
<p>API calls for registered clients must come from the specified website. This is how IRIS authenticates clients.</p>
<form action="#" method="post">
	<div class="row">
		<label>Name</label><input name="name" value="<?php echo $name;?>" type="text" maxlength="500" />
	</div>
	<div class="row">
		<label>Website</label><input name="website" value="<?php echo $website;?>" type="text" maxlength="500" value="http://"/>
	</div>
	<div class="row">
		<label>E-mail</label><input name="email" value="<?php echo $email;?>" type="email" />
	</div>
	<div class="row">
		<input type="submit" value="Register" />
	</div>
	<div class="row"></div>
</form>