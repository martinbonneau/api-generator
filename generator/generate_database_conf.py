import functions as Engine

from sys import exc_info
from shutil import copyfile as Copyfile



def launch():
    try:

        host        = Engine.get_answer("Hôte de la base de donnée (default : localhost') : ", "localhost")
        db_name     = Engine.get_answer("Nom de la base de données : ")
        username    = Engine.get_answer("Nom d'utilisateur de la base de données : ")
        password    = Engine.get_answer("Mot de passe : ")

        values = {}

        values["host"]      = host
        values["db_name"]   = db_name
        values["username"]  = username
        values["password"]  = password


        print("\nGenerating database configuration file...")

        Copyfile("./skeletons/config-database_skeleton.php", "../api/config/database.php")

        file_path = "../api/config/database.php"
        Engine.replace_in_file(file_path, values)

    except:
        print("Fatal error : " + str(exc_info()[0]))
        exit(1)
