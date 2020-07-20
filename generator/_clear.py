#delete all generated files


from sys import exc_info

from os import remove as RemoveFile
from os.path import exists
from os import listdir
from os.path import isfile


def rm(path, f):
    if(exists(path + f) and isfile(path+f)):
        print("removing " + f)
        RemoveFile(path + f)



try:
    rm("../api/config/", "database.php")
    rm("../api/config/", "schemas.sql")
    rm("../api/config/", "core.php")
    
    rm("../api/", "validate_token.php")

    for f in listdir("../api/objects"):
        rm("../api/objects/" , f)

    for f in listdir("../api/"):
        rm("../api/" , f)
        
except:
    print("Fatal error : " + str(exc_info()[0]))


