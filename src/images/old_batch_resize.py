#!/usr/bin/env python3
__author__ = 'Emil Bratt Boersting'
import os, sys, shutil, sqlite3, requests, json
from datetime import datetime
from time import sleep
try: # CHECK IF PILLOW IS INSTALLED
    from PIL import Image
except ModuleNotFoundError:
    with open('../logs/image_resize.log', 'a') as log:
        log.write(datetime.now().strftime("%d/%m/%Y %H:%M:%S") +' no module found for Pillow\n')
    exit()


############## SETTINGS #######################################################
global max_limit, host, port, source_path, target_path, format
max_limit = 500000000 # ADJUST IF YOU GET DECOMPRESSION BOMB WARNING
host = 'localhost'
port = '81'
db_name = '../admin/database.sqlite' # SET DATABASE NAME AND DIRECTORY
source_path = '../images/original' # SET INPUT DIRECTORY
target_path = '../images/converted' # SET OUTPUT DIRECTORY
format = "png" # SET OUTPUT FORMAT
###############################################################################


def fetch_website_res():
    # GET WEBSITE VALID IMAGE RESOLTUIONS FROM PHP CONFIG FILE AS JSON OBJECT
    response = requests.get('http://localhost:81/admin/config.inc.php?config=IMAGE_RES')
    if response.status_code != 200:
        with open('../logs/image_resize.log', 'a') as log:
            log.write(datetime.now().strftime("%d/%m/%Y %H:%M:%S") +' Could not load resolutions from config.inc.php\n')
        exit()

    res = json.loads(response.text)
    resolutions = []
    for v in res:
        resolutions.append(int(res[v]))

    return resolutions



# THIS FUNCTION RUNS AT THE START OF THE SCRIPT
def script_execute():
    Image.MAX_IMAGE_PIXELS = max_limit
    resolutions = fetch_website_res()

    # CREATE OUTPUT DIRECTORY IF NOT EXIST
    if not os.path.isdir(target_path):
        os.mkdir(target_path)

    # CREATE DATABASE IF NOT EXIST
    if not os.path.isfile(db_name):
        with open('../logs/image_resize.log\n', 'a') as log:
            log.write(datetime.now().strftime("%d/%m/%Y %H:%M:%S") + 'database ' + db_name + ' does not exist')

    cnxn = sqlite3.connect(db_name)
    cur = cnxn.cursor()

    # INSERT INTO DATABASE WHERE NEW ORIGINAL IMAGES ARE FOUND ON FILESYSTEM
    db_targets = []
    for row in cur.execute('SELECT org_target FROM image_org;'):
        db_targets.append(os.path.normpath(row[0]))



    org_targets_fs = []
    for dir_1 in os.listdir(source_path):
        for image in os.listdir(os.path.join(source_path,dir_1)):
            path = os.path.join(source_path,dir_1,image)
            org_targets_fs.append(path)
            if path not in db_targets:

                # OPEN ORIGINAL IMAGE
                img = Image.open(path)

                # EXTRACT ORIGINAL WIDTH AND HEIGHT
                width = img.size[0]
                height = img.size[1]

                # GET ASPECT INFO
                aspect = get_aspect(width, height)

                # CLOSE IMAGE OBJECT
                img.close()

                # INSERT INTO DATABASE
                cur.execute('''
                    INSERT INTO image_org
                    (file_name,category,aspect,width,height, org_target)
                    VALUES
                    (?,?,?,?,?,?);
                    ''', (image, dir_1, aspect,width, height, path)
                    )

    # COMMIT INSERTS
    cnxn.commit()

    # DELETE FROM DATABASE WHERE ORIGINAL IMAGE ON FILESYSTEM IS DELETED
    for target in db_targets:
        if os.path.normpath(target) not in org_targets_fs:
                    cur.execute('''
                DELETE FROM image_org
                WHERE org_target =?;
                ''', [target]
                )

    # COMMIT DELETES
    cnxn.commit()


    # INITIALIZE (SCAN FOLDER FOR NEW IMAGES) AND PREPARE RESIZING
    convert_object = Convert(source_path,target_path,format,resolutions, db_name)

    # IF NEW IMAGES -> CONVERT
    if convert_object.process_list != []:
        convert_object.process()
    else:
        print('No images to process')
        with open('../logs/image_resize.log', 'a') as log:
            log.write(datetime.now().strftime("%d/%m/%Y %H:%M:%S") +' No images to process\n')

    # DELETE RESIZED IMAGE FROM FILESYSTEM WHERE ORIGINAL NOT EXISTS
    for dir_1 in os.listdir(target_path):

        if len(os.listdir(os.path.join(target_path,dir_1))) == 0:
            shutil.rmtree(os.path.join(target_path,dir_1))
            break

        for dir_2 in os.listdir(os.path.join(target_path,dir_1)):
            if len(os.listdir(os.path.join(target_path,dir_1,dir_2))) == 0:
                shutil.rmtree(os.path.join(target_path,dir_1,dir_2))
                break

            for image in os.listdir(os.path.join(target_path,dir_1,dir_2)):
                path = os.path.join(target_path,dir_1,dir_2,image)
                target_image = os.path.splitext(image)[0]

                try:
                    source_images = [os.path.splitext(f)[0] for f in os.listdir(
                    os.path.join(source_path,dir_2)
                    )]

                    if target_image not in source_images:
                        os.remove(path)
                except FileNotFoundError:
                    shutil.rmtree(os.path.join(target_path,dir_1,dir_2))
                    break



    # DELETE FROM DATABASE WHERE RESIZED IMAGES ARE NOT FOUND IN DATABASE
    resize_targets_db = []
    for row in cur.execute('SELECT resize_target FROM image_resize;'):
        resize_targets_db.append(row[0])
        # resize_targets_db.append(os.path.normpath(row[0]))

    for path in resize_targets_db:
        if not os.path.isfile(path):
            cur.execute('''
            DELETE FROM image_resize
            WHERE resize_target =?
            ''', [path]
            )

    # COMMIT DELETES
    cnxn.commit()


    # CLOSE CONNECTION
    cnxn.close()


    with open('../logs/image_resize.log', 'a') as log:
        log.write(datetime.now().strftime("%d/%m/%Y %H:%M:%S") + ' Script ended succesfully\n')
    print('Script ended succesfully')

# END menu()


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


class Convert:
    def __init__(
        self,source_path,target_path,
        format,resolutions, db_name = False
        ):
        # BASE FOLDERS FOR IMAGE INPUT AND OUTPUT
        self.source_path = source_path
        self.target_path = target_path

        # PNG, JPEG OR ANY FORMAT THAT PILLOW SUPPORTS
        self.format = format

        # FOR SPECIFIED RESOLUTIONS
        self.resolutions = resolutions

        # FOR LOADING BAR
        self.jobs = False

        # FOR DATABASE CONNECTION
        self.db_name = db_name

        # PATHS FOR ALL IMAGES BEING PROCESSED
        self.process_list = []

        for res in self.resolutions:

            # CREATE BASE RESOLUTION DIRECTORY IF NOT EXIST
            if not os.path.isdir(os.path.join(self.target_path, str(res))):
                os.mkdir(os.path.join(self.target_path, str(res)))

            # FOR EVERY SUB DIR -> CATEGORY
            for dir_1 in os.listdir(self.source_path):

                # CREATE CATEGORY DIRECTORY
                if not os.path.isdir(os.path.join(
                self.target_path,str(res),dir_1)
                ):
                    os.mkdir(os.path.join(
                    self.target_path,str(res),dir_1
                    ))

                # FOR EVERY INPUT IMAGE
                for image in os.listdir(os.path.join(
                self.source_path,dir_1)
                ):

                    # SET RESOLUTION OUTPUT DIRECTORY IN VARIABLE
                    # cur_folder = os.path.join(
                    #     self.target_path,str(res),dir_1
                    # )

                    # SLICE EXTENTION AND STORE ONLY IMAGE NAME IN LIST
                    files = [
                        f.split('.')[0] for f in os.listdir(
                        os.path.join(
                        self.target_path,str(res),dir_1
                        )
                    )]

                    # CHECK IF ORIGINAL FILE EXIST IN CONVERTED FOLDER
                    if image.split('.')[0] not in files:
                        # GET PATH FOR INPUT AND OUTPUT
                        in_path = os.path.join(
                            self.source_path,dir_1,image
                        )
                        out_path = os.path.join(
                            self.target_path,str(res),dir_1
                        )
                        self.process_list.append(
                            [
                                image.split('.')[0],
                                in_path,out_path,
                                res
                            ]
                        )
    # END __init__()

    def process(self):

        image_data = []
        for list in self.process_list:

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
            new_name = original_name + '.' + self.format
            output_image_path = os.path.join(out_path, new_name)

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

            # APPEND ROW THAT WILL BE PROCESSED TO DATABASE INSERT
            image_data_row.append(original_image_path)
            image_data_row.append(output_image_path)
            image_data_row.append(new_name)
            image_data_row.append(self.format)
            image_data_row.append(new_res)
            image_data.append(image_data_row)

            with open('../logs/image_resize.log', 'a') as log:
                log.write(datetime.now().strftime("%d/%m/%Y %H:%M:%S") + output_image_path + '\n')

        if self.db_name != False:

            cnxn = sqlite3.connect(self.db_name)
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
                    -- image_resize.resize_target
                    -- image_resize.file_name,
                    -- image_resize.format,
                    -- image_resize.long_edge
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

# END Class Convert()


if __name__ == '__main__':
    script_execute()

# DATABASE SCHEMA
'''

    CREATE TABLE IF NOT EXISTS image_org
    (
        id_image        INTEGER PRIMARY KEY,
        file_name       VARCHAR(36)         NOT NULL, -- example: photo.tiff
        category        VARCHAR(36)         NOT NULL, -- example: nature
        aspect          VARCHAR(20)         NOT NULL, -- example: portrait
        width           SMALLINT UNSIGNED   NOT NULL, -- example: portrait
        height          SMALLINT UNSIGNED   NOT NULL, -- example: portrait
        org_target      VARCHAR(255)        NOT NULL, -- example: ./orig/path/photo.tiff
        date_insert     DATE DEFAULT CURRENT_DATE,
        time_insert     TIME DEFAULT CURRENT_TIME
    );

    CREATE TABLE IF NOT EXISTS image_resize
    (
        id_image        INTEGER NOT NULL,
        file_name       VARCHAR(36)         NOT NULL, -- example: photo.png
        format          CHAR(5)             NOT NULL, -- example: png
        resize_target   VARCHAR(255)        NOT NULL, -- example: ./resized/path/photo.png
        long_edge       SMALLINT UNSIGNED   NOT NULL, -- example: 800
        date_insert     DATE DEFAULT CURRENT_DATE,
        time_insert     TIME DEFAULT CURRENT_TIME,

        FOREIGN KEY (id_image)
            REFERENCES image_org (id_image)
                ON DELETE CASCADE
    );
'''


# SQL COMMANDS

# FIND PATH TO RESIZED IMAGES CONVERTED TO 800 PIXEL LONG EDGE
'''
SELECT
    image_resize.resize_target
FROM
    image_resize, image_org
WHERE
    image_resize.long_edge = '800'
AND
    image_resize.id_image = image_org.id_image
;
'''

# FIND PATH TO ALL RESIZED IMAGE 800, 1200 and 1600 PIXEL LONG EDGE FROM IMAGE ID
'''
    SELECT
        image_resize.resize_target
    FROM
        image_resize
    INNER JOIN
        image_org
    ON
        image_resize.id_image = image_org.id_image
    WHERE
        image_resize.long_edge == '800' AND
        image_org.id_image = '1'
    OR
        image_resize.long_edge == '1200' AND
        image_org.id_image = '1'
    OR
        image_resize.long_edge == '1600' AND
        image_org.id_image = '1'
    ;
'''

# FIND PATH TO ALL RESIZED SQUARED IMAGES WITH 800, 1200 and 1600 PIXEL LONG EDGE
'''
    SELECT
        image_resize.resize_target
    FROM
        image_resize
    INNER JOIN
        image_org
    ON
        image_resize.id_image = image_org.id_image
    WHERE
        image_resize.long_edge == '800' AND
        image_org.aspect = 'box'
    OR
        image_resize.long_edge == '1200' AND
        image_org.aspect = 'box'
    OR
        image_resize.long_edge == '1600' AND
        image_org.aspect = 'box'
    ;
'''
