##Form Keeper: Field Name Obfuscation for CakePHP Forms
I don't like giving my end users information about the inner workings of the system they're using. Even though the system itself might be secure in terms of what data it can take in, how it treats the data and what it returns, I still don't like the fact extra, inner system specs are publicly visible to the end user.

##What Is Form Keeper?
This is where Form Keeper steps in. Form Keeper is a CakePHP 1.3 plugin that takes over from where CakePHP's awesome Form Helper left off. Usually your forms would be constructed in a way like:

	<input name="data[ModelName][field_name]" id="ModelNameFieldName" value="" type="hidden" />

This is all well and good. PHP will take those posted results, turn the data into a multidimensional array, pass it onto CakePHP and CakePHP will handle the rest, like a boss. The only problem with this is, it leaves a bad feeling in my stomach, in that, the end user knows your models, your field names in the database. Now there may be no immediate security threat with that, but less information provided, means less information for the end user to get construct blueprints of your database. So with FormKeeper you get:

	<input name="6a03949c66c7cc5c7bce550c21308659c1d848a7" type="text" maxlength="40" id="6a03949c66c7cc5c7bce550c21308659c1d848a7">

##What FormKeeper Does
Form Keeper takes your forms, with all the power of CakePHP's FormHelper, and adds a layer of one way encryption to your field names and field id's. These values are firstly SHA1'd, cached locally into a hash table of your choice (using CakePHP's cache engine, also check out my CakePHP SuperStack plugin for some cache redundancy action), then outputted to the view. When the form is then submitted, FormKeeper checks the value is in the hash table, reconstructs the data multidimensional array, and inserts the values.

Now if you are thinking "this is all good, but CakePHP's security component is a mean bastard when it comes to forms and blackhole-ing", well that part is covered. The tokenizing works just fine with this plugin, so you can use the security component alongside the FormKeeper for obfuscation and form tampering fun.

##Setting FormKeeper Up
This is all well and good, but how do I set FormKeeper up? Quite easily, FormKeeper is a CakePHP plugin, so all you need to do is clone the Github repository to your plugins directory and then include both the plugin's helper and component elements to either your AppController (if you want to use it site wide) or to the controller of your choice.

	git clone https://github.com/voidet/form_keeper app/plugins/form_keeper

Or if you use submodules (like you should):

	git submodule add https://github.com/voidet/form_keeper app/plugins/form_keeper
	git submodule init
	git submodule update

From there all that is left is adding it to your controller(s):

	<?php

	class UsersController extends AppController {

		public $components = array('FormKeeper.FormKeeper');
		public $helpers = array('FormKeeper.FormKeeper');

Now user views have access to the FormKeeper power! But how do views use it? Easy, instead of $this->Form, you will now use $this->FormKeeper, for everything!

	<?php

	echo $this->FormKeeper->create();
	echo $this->FormKeeper->input('username');
	echo $this->FormKeeper->input('password');
	echo $this->FormKeeper->input('remember_me', array('type' => 'checkbox'));
	echo $this->FormKeeper->end('Login');

##Configuration
There are two parameters (so far) that you can change for the plugin. It's as simple as adding in a configuration file into your app/config folder, named form_keeper.php, app/config/form_keeper.php. The two parameters that can be changed is the salt string to encrypt with (if not provided the Security.salt value in core.php will be used) and the cache config that you want to use (by default it will be the default core cache config, which is usually file). So create app/config/form_keeper.php and add in:

	<?php

	$config['FormKeeper'] = array(
		'salt' => 'ixNE257AeJI2mbVicRwjEFM169seG59lzmxP8N4Z',
		'cacheKey' => 'default',
	);

Also please note that the Id's of the input field are also hashed. To avoid this, you can simply provide an id in your input options, as you would to override cake's default ID. This is primarily used for object selection in the DOM with jQuery etc.

##Thats It!
This is an early release of the plugin, and is not tested in production just yet. Expect many updates to come in the near future, and hopefully some test cases to be written. Also if you're using the security component, then please include it after the FormKeeper component at this point in time, as that is what has been *thoroughly* tested with. Thank you in advance for any comments and feedback left! If you're having issues with the plugin, I usually respond pretty fast with updates or advice. Enjoy!