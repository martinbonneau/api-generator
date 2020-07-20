import functions as Engine

from shutil import copyfile as Copyfile
from os.path import isfile


# initialize the api

if (not isfile("../api/config/schemas.sql")):
    Copyfile("./skeletons/schemas.sql", "../api/config/schemas.sql")
    

Copyfile("./skeletons/index.html", "../api/index.html")
Copyfile("./skeletons/login_skeleton.php", "../api/login.php")
Copyfile("./skeletons/refresh_login_skeleton.php", "../api/refresh_login.php")


print("Hello, let's me generate the skeleton of your api. Yes yes, it's my job.")

################################### DATABASE CONFIGURATION FILE ###################################
import generate_database_conf

answer = Engine.get_answer("\nOk, first, let's configure your database access ? (Y/n)", "Y")

if (answer.lower() == "y"):
    generate_database_conf.launch()
    input("Good job Franky ! Take a look to the ../api/conf/database.php file if your configuration is ok.")

###################################################################################################




########################################### CLASS FILES ###########################################
import generate_class

input("\n\nFine, now we're going to generate somes classes of you're database schemas.")
input("I will generate for you classes in ../api/objects/")
answer = Engine.get_answer("Let's start, ok ? (Y/n)", 'Y')


if (answer.lower() == "y"):
    input("\nWARNING : if you enter a \"password\" attribut name, the field is automatically hashed")
    add_more = generate_class.launch()

    while (add_more):
        input("Good job Sissy ! Take a look to the ../api/objects/ folder if your class generation is ok.\n\n")
        add_more = generate_class.launch()

###################################################################################################





######################################### SET DOMAIN NAME #########################################
import set_domain_name

input("\n\nFiou, it's ended of all theses classes ! We're going to change something less fastidious !")
answer = Engine.get_answer("Change the domain name ? (Y/n)", 'Y')

if(answer.lower() == 'y'):
    set_domain_name.launch()
    input("\nOof, a good thing !")
###################################################################################################



input("Hmm... It looks like you... you have ended the automatisation tool !! Congratulation my boy !")
input("But not so fast, because the fun is not ended : take a look to the \"and_now\" file for finish your API quest !")
input("See you later my friend.")
