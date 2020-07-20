import functions as Engine
from shutil import copyfile as Copyfile

from sys import exc_info


def launch():
    domain_name = Engine.get_answer("What's the FQDN where the site will be running ? (example : flower.grass.net) ")

    values = {}
    values["domain_name"] = domain_name.lower()


    print("Applying domain name...")


    # /api/config/core.php
    Copyfile("./skeletons/config-core_skeleton.php", "../api/config/core.php")

    Engine.replace_in_file("../api/config/core.php", values)

    print("\t/api/config/core.php")



    # /api/validate_token.php
    Copyfile("./skeletons/validate_token_skeleton.php", "../api/validate_token.php")

    Engine.replace_in_file("../api/validate_token.php", values)

    print("\t/api/validate_token.php")

