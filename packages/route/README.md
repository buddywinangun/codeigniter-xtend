# CodeIgniter Xtend routes and filters

A little group or libraries that let Codeigniter work as a static routing system with filters as simmilar to the Laravel routing system as I could have done.

## Future plans

- Caching routes.

## Installation

Just put copy the files into your server using the same folder structure. If the developer have a previously installed module system as HMVC the developer could have to overwrite the MY_Route files. If that's the case and the developer have any trouble making it, just ask for directions about that.

## Routing tutorial

The developer can use all the HTML methods to define a Route. This route will only be generated if the query of the page coincides with it's method. Meaning that if the developer make a GET call to the server, the library will only make the GET routes, leaving the rest as not existant. This also can be used by the developer to make a RESTFUL server.

### Basic routing

The basic methods are:


	Route::get('user', 			'user/index');
	Route::post('user/(:any)', 	'user/load/$1');
	Route::put('user/(:any), 	'user/update/$1');
	Route::delete('user/(:any)','user/delete/$1');
	Route::head('user', 		'user/index');
	Route::patch('user/(:any), 	'user/update/$1');
	Route::options('user/(:any),'user/load/$1');

The developer can also use two additional functions that let the route to be generated in more than one method:

`any` will work with any HTTP method (GET, POST, PUT, DELETE...).


 	Route::any('user', 			'user/index');

`match` lets the developer to define manually wich methods will be accepted by this route.


 	Route::match(array('GET', 'POST'), 'user', 'user/index');


### Subdomain routing

It uses the same routing method as the Basic routing but adding an extra wildcard for the subdomain.
Adding this extra option means that the routes with them won't show on urls without subdomain.

	Route::any('user',			'user/index', array('subdomain' => '(:any)'));


### Named routes

The developer can name a route. That will let him to call this name instead of using all the route in the future.



 	Route::set_name('user_update', 'admin/user/load/');

 	Route::get('user', 'user/index', array('as' => 'user'));


Calling a named route:


 	echo Route::named('user');

 	redirect(Route::named('user'));

Optionally, if that route has uri parameters, you can set them via array at the second parameter of the method:


redirect(Route::named('user', array('12')));

### Named parameters

The developer can also define named parameters in each route instead of using wildcards or regular expressions. This will let the developer use them also in the URI with their defined names.
If the developer don't define a named parameter, it will be used a an `(:any)` in the route.


	Route::any('user/{id}',		'user/load/$1');


There are two kinds of parameter definitions. The global definition, that will set a wildcard or regular expression to every named parameter with that name in the routes file and the local definition, that will only affect to the Route in wich it's defined.

Global parameter definition:


	Route::pattern('id',		'[0-9]+');
	Route::pattern('name',		'(:any)');


Local parameter definition:


	Route::post('user/{id}', 	'user/load/$1')->where('id', '[0-9]+');


Multiple local parameter definition;


	Route::post('user/{id}/{name}', 	'user/load/$1/$2')->where(array('id' => '[0-9]+', 'name' => '(:any)'));


### Optional parameters

There can be defined optional parameters. That will let Codeigniter use that route as much as there is or isn't a URI defined in that position.
The parameter definition is the same as the normal Named Parameters (the parameter name without the question mark "?").


	Route::any('user/{id?}', 	'user/load/$1')->where('id', '[0-9]+');


This will let the developer use "user" and "user/12" as routes using Codeigniter the method load in the controller user in both calls.

The developer can also stack multiple optional parameters like this and all the possible permutations of the optional uris will be defined automatically:


	Route::any('user/{id?}/{name?}/{telephone?}', 	'user/load/$1/$2/$3')->where('id', '[0-9]+');


### Accessing named and optional parameters

Codeigniter will store a list of this parameters in the URI library, so the developer can access them using their name instead of the position in the uri segment.


	$user_id = $this->uri->segment('id');

	$user_name = $this->uri->rsegment('name');


### Route filters

The developer can also define route filters. That will execute the defined methods before or after the controller execution.
This is useful for adding additional info or making previous checks, like if the user is logged in.

Filter definition:


	Route::filter('logged_in', function(){

		if($this->auth->logged_in() == FALSE)
		{
			show_404();
		}

	});

This code will be executed immediately after the CI_Controller construction or after the method calling, so the developer can use all the auto loaded files and also load new ones with `$this->load` if it's neccesary.

Adding a filter to a route:

The developer can define two filter callings, `before` a route calling and `after` the route execution.


	Route::any('user/{id}',	'user/load/$1', array('before' => 'logged_in'));


This will launch the logged_in check before Codeigniter calls the `load` method, so if the user it's not logged in, instead will receive a 404 error display.

Passing multiple filters


	Route::any('user/{id}',	'user/load/$1', array('before' => array('logged_in', 'check_params')));
	Route::any('user/{id}',	'user/load/$1', array('after' => 'logged_in|check_params'));

Anonymous function filters

	Route::any('user/{id}',	'user/load/$1', array('before' => function(){
																			// your code here
																		}));


Specifying filter parameters

All filters always receive as first parameter the uri string of the route. But it's possible to set manually more parameters to send to the filter method.

Sometimes it's useful to send parameters to the filter anonymous functions. This can be easily made as:

	Route::any('user/{id}',	'user/load/$1', array('before' => 'logged_in[user_name]'));

The developer can also add uri segments as parameters putting the number or name of the segment between {}. The filter parameters will be separated with the ":" character

	Route::any('user/{id}',	'user/load/$1', array('before' => 'logged_in[user_name:{id}]'));

Now the parameter will look like:

	Route::filter('logged_in', function($uri, $user_name, $id){

		var_dump($uri, $user_name, $id);

		if($this->auth->logged_in() == FALSE)
		{
			show_404();
		}

	});


### Route groups

This lets the developer add a prefix to every route inside it. This it's useful for defining admin routes, for example.


	Route::prefix('admin', function(){

		Route::prefix('user', function(){

				Route::post('update/(:any)', 'user/update/$1');

		});

	});


Another way of define route groups


	Route::any('admin', 'admin/index', array(), function(){

		Route::any('user', 'user/index');

	});


### RESTFUL like routes


	Route::resources('photos', $options);


This will autocreate all this routes:

```
  GET     /photos         index       displaying a list of photos
  GET     /photos/new     create_new  return an HTML form for creating a photo
  POST    /photos         create      create a new photo
  GET     /photos/{id}    show        display a specific photo
  GET     /photos/{id}/edit   edit    return the HTML form for editing a single photo
  PUT     /photos/{id}    update      update a specific photo
  DELETE  /photos/{id}    delete      delete a specific photo
```

  The $options parameter it's optional and can hold the same values as any other $options parameter
  of the rest of route methods (like filters, etc...).


### Auto routes

The developer can make with one call a 6 tier route definition


	Route::context('photos', 'photo');

```
  photo/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)    	$1/photo/$2/$3/$4/$5/$6
  photo/(:any)/(:any)/(:any)/(:any)/(:any)				$1/photo/$2/$3/$4/$5
  photo/(:any)/(:any)/(:any)/(:any)						$1/photo/$2/$3/$4
  photo/(:any)/(:any)/(:any)							$1/photo/$2/$3
  photo/(:any)/(:any)									$1/photo/$2
  photo/(:any)											$1/photo
```

### Block routes

Not very useful now that Codeigniter only has static routes with this library, but the developer can also block manually routes.


	Route::block('user/(:any)');

Will generate a blank route to this uris that, eventually, will led to a 404 display error.


### Additional route files

The library will search for a folder named "routes" at "application/". Also will open all files listed inside it, so if the developer want's to have more than one file to keep an organized route file structure he can deploy the files there and all will be processed by the Routes library.

Important note: the `Route::map()` call method must be always placed only at the "/application/config/routes.php" file.