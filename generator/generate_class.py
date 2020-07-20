import functions as Engine
from shutil import copyfile as Copyfile
from math import floor as Round_down

from sys import exc_info


def launch():
    #try:

    print("Note : leave field empty to finish")

    classname = input("Name of your object : ")
    if (classname == ""):
        return False # don't add more classes

    attributs = []

    attrname = input("\tName of the attribut : ")
    while(attrname != ""):
        attributs.append(attrname.lower())
        attrname = input("\tName of the attribut : ")


    values = {}
    values["classname_capitalize"] = classname.capitalize()
    values["classname_lower"] = classname.lower()
    values["table_name"] = classname + 's'

    #replace attributs class
    txt_attr = "\tpublic $id;\n"
    for attr in attributs:
        txt_attr += "\tpublic $" + attr + ';\n'
    values["txt_attr"] = txt_attr

    query_attr = ""
    #replace attributs in the query
    for attr in attributs:
        query_attr += "\t\t\t\t" + attr + " = :" + attr + ",\n"
    query_attr = query_attr[:-2] #remove last comma and \n
    values["query_attr"] = query_attr

    sanitize_attr = ""
    #replace attributs in the sanitize section
    for attr in attributs:
        sanitize_attr += "\t\t\t\t$" + classname.lower() + "->" + attr + "=htmlspecialchars(strip_tags($data->" + attr + "));\n"
    values["sanitize_attr"] = sanitize_attr

    bind_attr = ""
    #replace attributs in the bind value (in query) section
    for attr in attributs:
        bind_attr += "\t\t$stmt->bindParam(':" + attr + "', $this->" + attr + ");\n"
    values["bind_attr"] = bind_attr


    #replace attributs in the /api/object.php#switch/case#POST#second_if
    issets = ""
    for attr in attributs:
        issets += "\t\t\t\tisset($data->" + attr + ") &&\n"
    issets = issets[:-4] #remove last && and \n
    values["issets"] = issets


    #replace attributs when a password field is specified
    if("password" in attributs):
        values["hash_password"] = "\t\t\t\t//hash password\n\t\t\t\t$" + classname.lower() + "->password = password_hash($" + classname.lower() + "->password, PASSWORD_BCRYPT);"

        values["clear_password"] = "\t\t\t\t\t//clear password before send result\n\t\t\t\t\tunset($" + classname.lower() + "->password);"

    else:
        values["hash_password"] = ""
        values["clear_password"] = ""



    #######################################################
    #class generation
    print("\tGenerating class...")

    Copyfile("./skeletons/objects-object_skeleton.php", "../api/objects/" + classname.lower() + ".php")

    file_path = "../api/objects/" + classname.lower() + ".php"
    Engine.replace_in_file(file_path, values)


    #######################################################
    #endpoint generation
    print("\tGenerating endpoint...")

    Copyfile("./skeletons/endpoint_skeleton.php", "../api/" + classname.lower() + ".php")

    file_path = "../api/" + classname.lower() + ".php"
    Engine.replace_in_file(file_path, values)




    #######################################################
    #sql schema

    print("\tGenerating sql schema...")

    table = "CREATE TABLE " + classname.upper() + "S (\n\tID\t\t\t\tINT\t\t\t\tNOT NULL AUTO_INCREMENT,\n"

    for attr in attributs:
        tabulation_number = Round_down(5 - len(attr) / 3)
        if(tabulation_number < 0): tabulation_number = 1
        table += "\t" + attr.upper() + tabulation_number*"\t" + "TYPE()\t\t\tNOT NULL,\n"


    table += "\n\tCONSTRAINT PK_" + classname.upper() + "S PRIMARY KEY (ID)\n);"

    f = open("../api/config/schemas.sql", "a")
    f.write("\n\n" + table)
    f.close()




    print("Done !\n")
    return True

    #except:
    #    print("Fatal error : " + str(exc_info()[0]))
    #    exit(1)
