from sys import exc_info

def replace_text(vars:dict, text:str):

    for key, value in vars.items():
        key = '$' + key + '$'
        text = text.replace(key, value)
    
    return text

#end replace(vars, text)



def replace_in_file(file, values):
    try:
        f = open(file, 'r')
        f_content = f.read()
        f.close()

        f_content = replace_text(values, f_content)

        f = open(file, 'w')
        f.write(f_content)
        f.close()
    except:
        print("Error in replacing variables in file " + file + "\nError : " + str(exc_info()[0]))
        exit(1)
#end replace_in_file(file, new_text)

def get_answer(question, default="") -> str:

    answer = ""

    while (answer == ""):
        answer = input(question)

        if (answer == "" and default != ""):
            answer = default
    
    return answer

#end get_answer(question)