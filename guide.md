# Guided Steps

A word before :
When your api is ready, copy the following to a better place than this project folder :

- the `api` folder
- `.htaccess`


## Generate your APi


1. Create a new database in your SGBD

> For example, open PHPMyAdmin and create a new database
> Here we create the "musicshop" database

In this guide I use MySQL. You use an other SGBD ?
--> take a look to the `_ACTION_ #change_db` flag in the `/api/config/database.php` file when you ended the step 2

2. Open the "/generator" folder and start "_launch_api_generation.py"

> `cd generator`
> `python _launch_api_generation.py`

3. The programm will ask you for database configuration. Select 'y'

> Database host : host where you database can accept requests. For me : localhost
> Database name : name you setted in step 1. Here : musicshop
> Username / password : I can't know that for you

4. The programm will ask you for generating objects. Select 'y'

Let's create a **user** and a **music** objects

Hint : press enter when you entered all attributs

> Name of your object : user
>         Name of the attribut : mail  
>         Name of the attribut : password
>         Name of the attribut : name
>         Name of the attribut : pseudo


> Name of your object : music 
>         Name of the attribut : composer
>         Name of the attribut : genre
>         Name of the attribut : length


When ask again for a new object, press enter to continue

5. The programm will asking you for change domain name. Select 'y'

Enter your FQDN or `*` if you don't know


6. Your API is almost ready, but you don't have any table in schemas !

Because the tool is friendly, it generate for you a template of your queries to insert tables.
Open the `/api/config/schemas.sql` file and complete it with your attributs types.
Add some constraints if needed.

> Hint : set the PASSWORD field type to VARCHAR(2048) 

At the end of the file, add an extra table "refresh token".
This table will contains users' refresh token and looks like following :

```SQL
CREATE TABLE REFRESH_TOKEN (
	ID				INT					NOT NULL AUTO_INCREMENT,
	RT				VARCHAR(50)			NOT NULL,
	EXPIRATION		VARCHAR(14)			NOT NULL,
	USER			INT					NOT NULL,

	CONSTRAINT PK_RT PRIMARY KEY (ID),
    CONSTRAINT FK_RT_USER__USER_ID FOREIGN KEY (USER) REFERENCES users(ID),
    CONSTRAINT UK_RT_USER UNIQUE (USER)
);
```

Connect to your SGBD (via PHPMyAdmin or your prefered way), and copy paste the content of the `schemas.sql` file.

Your tables are created !


7. Now you have your tables but.. You don't have any user in your USERS table !

Without any user, you'll can't login thought the API and can't have access to datas.
Add a user directly via PHPMyAdmin or other way.

> Find here an example user with `8878` password:


> INSERT INTO `users` (`NAME`, `PSEUDO`, `MAIL`, `PASSWORD`) VALUES ("Boris", 'Bobo77', 'boris@example.com', '$2y$10$p/xLewE8WtWzJr7mLyPH3uilv2M3Q4CLaUVU7Efw9CWL1/lX7ywyu')



8. Test the api !

Start postman or you favorite request tool.
First thing to do : get a token access for manipulate datas.

For all requests, I'll precise the method (GET, POST, ...), the access URL and the payload.
To set the payload in postman, go to "Body" section (under URL address) and select "raw".
You will see a text input, write the payload in it.


## Use the API

### LOGIN

```JSON
Method    : POST (but all works)
URL       : http://localhost/api/login
Payload   : { "mail" : "boris@example.com", "password" : "8878" }
```

Answer :
```JSON
{
    "message": "Successful login.",
    "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvKiIsImF1ZCI6Imh0dHBzOlwvXC8qIiwiaWF0IjoxNTg3MTU4Njk1LCJleHAiOjE1ODcyMTYyOTUsInN1YiI6IjEifQ.nXbUEb1842a7F8zxqKU1Hnl3Ijum5PKzkN_t5hQzxwE",
    "rt": "20200517232455.5e9a1ea7ef1084.78878672"
}
```

Now, copy the `"jwt" : "<token>"` line, you will need it for all future requests.
Everywhere you see the the JWT variable, replace it by yours.

#### I get "user doesn't exists" error

If you get this error, the `/api/login.php` file doesn't have same field that you send.
Go to the `_ACTION_ #login_fields` flag.



### GET all users

Try to get all users :

```JSON
Method    : GET
URL       : http://localhost/api/user
Payload   : { "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7ImlkIjoiMSIsIm1haWwiOiJib3Jpc0BleGFtcGxlLmNvbSJ9fQ.IT2qQqztRW9q8wVkF55_D0-Qj7O-cu-QHu_d7RNc9ZA" }
```

Like you can see, the entire object is returned. If you don't want that, check in `/api/objects/user.php` the `_ACTION_ #dont_return_all` flag.



### Create a new user

```JSON
Method    : POST
URL       : http://localhost/api/user
Payload   : { "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7ImlkIjoiMSIsIm1haWwiOiJib3Jpc0BleGFtcGxlLmNvbSJ9fQ.IT2qQqztRW9q8wVkF55_D0-Qj7O-cu-QHu_d7RNc9ZA",
               "name"      : "Sophia",
               "pseudo"    : "soso12",
               "mail"      : "sophia@example.com",
               "password"  : "7787"
             }
```

If you get "Access Denied" message, please verify your JSON structure

Return :
```JSON
{
    "id": "2",
    "mail": "sophia@example.com",
    "name": "Sophia",
    "pseudo": "soso12"
}
```


### Update an existing user

We'll update the Sophia user, her ID is '2' so we have to precise it in the request URL

```JSON
Method    : PUT
URL       : http://localhost/api/user/2
Payload   : { "jwt"       : "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7ImlkIjoiMSIsIm1haWwiOiJib3Jpc0BleGFtcGxlLmNvbSJ9fQ.IT2qQqztRW9q8wVkF55_D0-Qj7O-cu-QHu_d7RNc9ZA",
               "name"      : "Sophia",
               "pseudo"    : "soso77",
               "mail"      : "sophia77@example.com",
               "password"  : "7787"
             }
```

By default, we have to precise all arguments even the password field, it can be a security failure.
Go to `/api/user.php` and find the `_ACTION_ #update` flag to adjust it.

Return :
```JSON
{
    "id": "2",
    "mail": "sophia77@example.com",
    "name": "Sophia",
    "pseudo": "soso77"
}
```

### Delete our first user

```JSON
Method    : DELETE
URL       : http://localhost/api/user/1
Payload   : { "jwt"       : "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7ImlkIjoiMSIsIm1haWwiOiJib3Jpc0BleGFtcGxlLmNvbSJ9fQ.IT2qQqztRW9q8wVkF55_D0-Qj7O-cu-QHu_d7RNc9ZA"
             }
```

Return :
```JSON
{
    "message": "Successfully deleted",
    "id": "1"
}
```

The user is deleted.
But note that by default the token is alway valid even if the user is deleted !
If you don't want to allow this : check `/api/validate_token.php`, a `_ACTION_` flag explain how to do that.


# And after ?

You can adjust all of the code for answering to your needs !
If you don't know where to start, I help :

## Encapsulated datas

When we generate a token, by default, we encapsulate the ID of the user in it.
You can add more informations in the token, for doing that check the `_ACTION_ #encapsulates_datas` flag in the `/api/validate_token.php` file. You have to check in your userController (`/api/user.php`) the same comment if you add some datas.

## Change expiration time

By default, generated tokens are valid for 1 hour.
If you want to get more or less time check the `make_token()` method of `/api/validate_token.php` file.

Think to adapt the expiration time of the refresh token (30 days by default).

In both case, search `_ACTION_ #tokens_expiration`

## The refresh token

the Refresh Token (RT) is used to re-authentifiate a user.
This uniq token is associate in database to the user when JWT is generate.

If the user present both valids JWT and RT, the system generate a new valid pair
of JWT and RT.

To use re-auth with the RT :

```JSON
Method    : POST
URL       : http://localhost/api/refresh_login
Payload   : { "jwt"       : "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7ImlkIjoiMSIsIm1haWwiOiJib3Jpc0BleGFtcGxlLmNvbSJ9fQ.IT2qQqztRW9q8wVkF55_D0-Qj7O-cu-QHu_d7RNc9ZA",
              "rt": "20200517232455.5e9a1ea7ef1084.78878672"
             }
```


## Implement admin / user privileges

If your app need a difference between admin and user role type, this section is for you !

In theory, you only have to change the controller file (/api/<object>.php).
There is an example who implements user rights + method use restrictions.

In this example, users can only update itself even if they target a specific userID in the url.
Same thing for delete : they can't delete other people.
And finally, only administrators can create users.

Find this basic example implementation in the "example" folder.

**Note** : In addition, the example use other attributs for user : name, login, type, password
You can what are changes I made in the `login.php` file ;)

