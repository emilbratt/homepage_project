#!/usr/bin/env python3
__author__ = 'Emil Bratt Boersting'
import os, sys, shutil, sqlite3, requests, json, subprocess
from datetime import datetime
from time import sleep
try: # CHECK IF PILLOW IS INSTALLED
    from PIL import Image
except ModuleNotFoundError:
    with open('../logs/image_resize.log', 'a') as log:
        log.write(datetime.now().strftime("%d/%m/%Y %H:%M:%S") +' no module found for Pillow\n')
    exit()


############## SETTINGS #######################################################
global max_limit, category, target_path, format, config_URL
max_limit = 500000000 # ADJUST IF YOU GET DECOMPRESSION BOMB WARNING
# host = 'localhost'
# port = '81'
db_name = 'database.sqlite' # SET DATABASE NAME AND DIRECTORY
target_path = '../images/converted' # SET OUTPUT DIRECTORY
category = 'blog' # THIS SCRIPT IS FOR BLOG IMAGES
if(len(sys.argv) >= 4): # OPTIONALLY PASS CATEGORY NME AS 3RD ARGUMENT
    category = sys.argv[3]
format = "png" # SET OUTPUT FORMAT
if(len(sys.argv) >= 5): # OPTIONALLY PASS FORMAT TYPE AS 4TH ARGUMENT
    format = sys.argv[4]
config_URL = 'http://localhost:81/admin/config.inc.php?config=IMAGE_RES'
###############################################################################


def fetch_website_res():

    # USING GET METHOD FOR JSON REQUEST
    response = requests.get(config_URL)
    if response.status_code == 200:
        res = json.loads(response.text)
        resolutions = []
        for v in res:
            resolutions.append(int(res[v]))

    if response.status_code != 200:
        # FALLBACK TO USING SHELL COMMAND AND STDOUT
        stdout = subprocess.Popen("php config.inc.php IMAGE_RES", stdout=subprocess.PIPE, shell=True)
        stdout_grab = stdout.communicate()
        resolutions = stdout_grab[0].decode("utf-8").split(',') # CONVERT BYTE STRING OUTPUT TO STR
        del resolutions[0] # FIRST OBJET IS NEW LINE, DELETING IT
        for res in range(len(resolutions)):
            resolutions[res] = int(resolutions[res])

    return resolutions




def get_aspect(width, height):
    resolution = width * height
    if width > (height*1.8):
        aspect = 'wide' # panorama
    elif height > (width*1.8):
        aspect = 'tall' # tall rectangle
    elif width > (height*1.2):
        aspect = 'landscape' # normal landscape
    elif height > (width*1.2):
        aspect = 'portrait' # normal portrait
    else:
        aspect = 'box'
    return aspect



def process(process_list):

    image_data = []
    for list in process_list:

        # FOR USE WITH DATABASE INSERT
        image_data_row = []

        # GET VALUES FROM CURRENT PROCESS LIST
        original_name = list[0] # IMAGE NAME WITHOUT FILE ENDING
        original_image_path = list[1] # FULL PATH TO ORIGINAL IMAGE
        out_path = list[2] # OUTPUT PATH (INCLUDES OUTPUT RESOLUTION IN NAME)
        new_res = list[3] # OUTPUT WIDTH RESOLUTION

        # OPEN ORIGINAL IMAGE
        img = Image.open(original_image_path)

        # EXTRACT ORIGINAL WIDTH AND HEIGHT
        width_original = img.size[0]
        height_original = img.size[1]

        # GET ASPECT INFO
        aspect = get_aspect(width_original, height_original)

        # GENERATE NEW IMAGE NAME AND PATH BASED DIRECTORY AND INPUT FORMAT
        new_name = original_name + '.' + format
        output_image_path = os.path.join(out_path, new_name)

        if not os.path.isdir(out_path):
            os.mkdir(out_path)

        # CALCULATE NEW SIZE AND SET NEW BASE LENGTH OF SIDE BASED ON LONGEST SIDE
        if width_original >= height_original:
            new_width = new_res
            # GET PERCENTAGE WIDTH FROM NEW RES
            width_precent = new_res / float(width_original)

            # USE PRECENTAGE TO CALCULATE HEIGHT
            new_height = int( float(height_original) * float(width_precent) )
        else:
            new_height = new_res
            # GET PERCENTAGE HEIGHT FROM NEW RES

            height_percent = (new_res / float(height_original))
            # USE PRECENTAGE TO CALCULATE WIDTH

            new_width = int( float(width_original) * float(height_percent) )

        # RESIZE
        img = img.resize((new_width, new_height), Image.ANTIALIAS)

        # SAVE
        img.save(output_image_path)

        # CLOSE IMAGE OBJECT
        img.close()

        # APPEND ROW THAT WILL BE SENT TO DATABASE INSERT
        image_data_row.append(original_image_path)
        image_data_row.append(output_image_path)
        image_data_row.append(new_name)
        image_data_row.append(format)
        image_data_row.append(new_res)
        image_data.append(image_data_row)

        with open('../logs/image_resize.log', 'a') as log:
            log.write(datetime.now().strftime("%d/%m/%Y %H:%M:%S") + output_image_path + '\n')

    cnxn = sqlite3.connect(db_name)
    cur = cnxn.cursor()

    # SET COMMIT TO FALSE SO THAT IF NO NEW IMAGES, NO COMMITS WILL BE MADE
    commit = False
    for row in image_data:

        # GET CORRECT ID FROM MAIN IMAGE TABLE
        cur.execute('''
        SELECT id_image FROM image_org WHERE org_target =?
        ''', [row[0]]
        )
        id = cur.fetchone()[0]

        # IF THIS QUERY RETUNRS NO RESULT...
        cur.execute('''
        SELECT
            image_org.id_image,
            image_org.org_target
        FROM
            image_org
        LEFT JOIN
            image_resize
        ON
            image_org.id_image = image_resize.id_image
        WHERE
            image_org.org_target =? AND
            image_resize.resize_target =?
        ;
        ''', [row[0],row[1]])
        result = cur.fetchone()

        # ...THEN INSERT NEW IMAGE DATA FOR RESIZED IMAGE
        if result == None:

            # SET COMMIT TO TRUE SO THE COMMIT WILL TRIGGER
            commit = True

            # INSERT IMAGE DATA
            cur.execute("""
            INSERT INTO image_resize
            (
            	id_image,file_name,format,resize_target,long_edge
            )
            VALUES
            	(?,?,?,?,?)
            ;

            """,[id,row[2],row[3],row[1],row[4]])
            with open('../logs/image_resize.log', 'a') as log:
                log.write(datetime.now().strftime("%d/%m/%Y %H:%M:%S") + ' Database insert ' + row[1] + '\n')

    if commit == True:
        cnxn.commit()

    cnxn.close()

# END process()


def insert_org(file_name,original_image_path):

    img = Image.open(original_image_path)
    # width = img.size[0]
    # height = img.size[1]
    width = 234
    height = 234
    aspect = get_aspect(width, height)
    img.close()

    cnxn = sqlite3.connect(db_name)
    cur = cnxn.cursor()
    cur.execute('''
        INSERT INTO image_org
        (file_name,category,aspect, width, height, org_target)
        VALUES
        (?,?,?,?,?,?);
        ''', (file_name, category, aspect, width, height, original_image_path)
    )

    if category == 'profile':
        cur.execute('SELECT MAX(id_image) FROM image_org;')
        last_id = cur.fetchone()[0]
        cur.execute('UPDATE user_data SET profile_pic = ?;', str(last_id))

    cnxn.commit()
    cnxn.close()

# THIS FUNCTION RUNS AT THE START OF THE SCRIPT
def script_execute():
    Image.MAX_IMAGE_PIXELS = max_limit
    resolutions = fetch_website_res()

    if len(sys.argv) < 3:
        with open('../logs/image_resize.log', 'a') as log:
            log.write(datetime.now().strftime("%d/%m/%Y %H:%M:%S") + ' source path or/and target path argument was not passed\n')
        sys.exit(100)
        exit()

    file_name = sys.argv[1]
    source_target = sys.argv[2]

    insert_org(file_name,source_target)

    if not os.path.isdir(target_path):
        os.mkdir(target_path)

    process_list = []
    for res in resolutions:
        if not os.path.isdir(os.path.join(target_path,str(res))):
            os.mkdir(os.path.join(target_path,str(res)))
        process_list.append([
            file_name,
            source_target,
            os.path.join(target_path, str(res), category),
            res
        ])

    # CREATE OUTPUT DIRECTORY IF NOT EXIST
    if not os.path.isdir(target_path):
        os.mkdir(target_path)

    process(process_list)

    with open('../logs/image_resize.log', 'a') as log:
        log.write(datetime.now().strftime("%d/%m/%Y %H:%M:%S") + ' Script ended succesfully\n')


# END menu()

if __name__ == '__main__':
    script_execute()
