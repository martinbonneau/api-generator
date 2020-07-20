# Introduction
The goal of this project is to give a simple way to generate a real good skeleton base for a plain PHP API with JWT.


## What is generate ?
- A skeleton of your database schema (sql),
- Your database configuration access,
- Your objects,
- Detect which type of request is sended (GET, PATCH, POST, ...),
- An authentication and token generation with JWT
- Only authentified users can access to db (do you need a user for first connection ? See bellow)
- A refresh token mechanism

Note : I really recommand you to use "user" as your user object's name.
If you don't want that, you'll have to modify SQL requests and maybe some code for authentication.
I don't have a list of needed changes, and I don't want to build one.

# How to use ?


> Please, take a look to the "guide.md" file, the procedure is
> more complete and some security subjects are explain

1. Go to the "generator" folder
2. Execute `python _launch_api_generation.py`
3. The tool will ask you questions, let's follow them
4. Copy (don't move, just copy) the `api` folder in a secure folder
5. Optionnal : restore the project folder as it's initial state (see bellow)
6. Adjust some files (see section bellow)
7. Enjoy!



## What are modifications do I need to operate ?

First, you have to modify the `schemas.sql` file.
Change the type of your attributs and add some constraints.
`/api/config/schemas.sql`

Second, take a look to different files.
Search in the code `_ACTION_` comments, all informations are in comments.
In theory, you just have to adjust (if needed) some informations. 
- `/api/login.php`
- `/api/objects/*`
- `/api/<obj>.php`
- `/api/validate_token.php`

All is detailled in guide.

# I want to clear files that are generated

Just execute `python _clear.py` and files will be removed.
Be carefull with this tool, there is no way to restore deleted files.
